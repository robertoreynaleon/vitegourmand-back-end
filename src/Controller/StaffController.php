<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\MenuDish;
use App\Entity\MenuImage;
use App\Entity\OrderMenu;
use App\Entity\User;
use App\Enum\DishType;
use App\Enum\OrderStatus;
use App\Repository\AllergenRepository;
use App\Repository\DishRepository;
use App\Repository\MenuRepository;
use App\Repository\OrderRepository;
use App\Repository\RegimeRepository;
use App\Service\MailService;
use App\Service\MongoDBService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/staff')]
class StaffController extends AbstractController
{
    /**
     * GET /api/staff/orders
     *
     * Retourne toutes les commandes pour le staff (STAFF_MEMBER ou ADMIN).
     */
    #[Route('/orders', name: 'api_staff_orders', methods: ['GET'])]
    public function orders(OrderRepository $orderRepository): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }

        $roles = $user->getRoles();
        if (!in_array('ROLE_STAFF_MEMBER', $roles, true) && !in_array('ROLE_ADMIN', $roles, true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $orders = $orderRepository->findBy([], ['orderDate' => 'DESC']);

        $data = array_map(fn($order) => [
            'id'              => $order->getId(),
            'clientName'      => $order->getUser()->getName() . ' ' . $order->getUser()->getLastname(),
            'orderDate'       => $order->getOrderDate()?->format('d/m/Y H:i'),
            'deliveryDate'    => $order->getDeliveryDate()?->format('d/m/Y'),
            'deliveryTime'    => $order->getDeliveryTime()?->format('H:i'),
            'deliveryAddress' => $order->getDeliveryAddress(),
            'status'          => $order->getStatus()->value,
            'totalAmount'     => $order->getTotalAmount(),
        ], $orders);

        return new JsonResponse($data);
    }

    /**
     * GET /api/staff/orders/{id}
     *
     * Retourne le détail complet d'une commande (items inclus) pour le staff.
     */
    #[Route('/orders/{id}', name: 'api_staff_order_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function orderShow(int $id, OrderRepository $orderRepository): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }

        $roles = $user->getRoles();
        if (!in_array('ROLE_STAFF_MEMBER', $roles, true) && !in_array('ROLE_ADMIN', $roles, true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $order = $orderRepository->find($id);
        if (!$order) {
            return new JsonResponse(['message' => 'Commande introuvable.'], 404);
        }

        $client = $order->getUser();

        $items = array_map(fn(OrderMenu $om) => [
            'menuTitle'      => $om->getMenu()->getTitle(),
            'quantity'       => $om->getQuantity(),
            'pricePerPerson' => (float) $om->getPricePerPerson(),
            'subtotal'       => round((float) $om->getPricePerPerson() * $om->getQuantity(), 2),
        ], $order->getOrderMenus()->toArray());

        return new JsonResponse([
            'id'               => $order->getId(),
            'orderDate'        => $order->getOrderDate()?->format('d/m/Y H:i'),
            'deliveryDate'     => $order->getDeliveryDate()?->format('d/m/Y'),
            'deliveryTime'     => $order->getDeliveryTime()?->format('H:i'),
            'deliveryAddress'  => $order->getDeliveryAddress(),
            'subtotal'         => $order->getSubtotal(),
            'deliveryFee'      => $order->getDeliveryFee(),
            'totalAmount'      => $order->getTotalAmount(),
            'equipmentLoan'    => $order->isEquipmentLoan(),
            'equipmentReturned' => $order->isEquipmentReturned(),
            'status'           => $order->getStatus()->value,
            'items'            => $items,
            'client' => [
                'name'       => $client->getName(),
                'lastname'   => $client->getLastname(),
                'email'      => $client->getEmail(),
                'phone'      => $client->getPhone(),
                'address'    => $client->getAddress(),
                'postalCode' => $client->getPostalCode(),
                'city'       => $client->getCity(),
            ],
        ]);
    }

    /**
     * PUT /api/staff/orders/{id}/treat
     *
     * Met à jour le statut, le prêt matériel et envoie un mail au client.
     * Si action = 'refuse', le statut est forcé à 'annulée'.
     */
    #[Route('/orders/{id}/treat', name: 'api_staff_order_treat', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function orderTreat(
        int $id,
        Request $request,
        OrderRepository $orderRepository,
        EntityManagerInterface $entityManager,
        MailService $mailService,
        LoggerInterface $logger,
        MongoDBService $mongoDBService
    ): JsonResponse {
        /** @var User|null $staff */
        $staff = $this->getUser();

        if (!$staff) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }

        $roles = $staff->getRoles();
        if (!in_array('ROLE_STAFF_MEMBER', $roles, true) && !in_array('ROLE_ADMIN', $roles, true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $order = $orderRepository->find($id);
        if (!$order) {
            return new JsonResponse(['message' => 'Commande introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $action          = trim((string) ($data['action']          ?? 'update'));
        $statusValue     = trim((string) ($data['status']          ?? ''));
        $equipmentLoan   = isset($data['equipmentLoan'])   ? (bool) $data['equipmentLoan']   : $order->isEquipmentLoan();
        $equipmentReturned = isset($data['equipmentReturned']) ? (bool) $data['equipmentReturned'] : $order->isEquipmentReturned();
        $staffMessage    = trim(strip_tags((string) ($data['staffMessage'] ?? '')));

        // Validation du message obligatoire pour refuser
        if ($action === 'refuse' && $staffMessage === '') {
            return new JsonResponse(['message' => 'Un message est obligatoire pour refuser une commande.'], 400);
        }

        // Résolution du statut enum
        if ($action === 'refuse') {
            $newStatus = OrderStatus::Annulee;
        } else {
            $newStatus = $order->getStatus();
            if ($statusValue !== '') {
                $resolved = OrderStatus::tryFrom($statusValue);
                if ($resolved === null) {
                    return new JsonResponse(['message' => 'Statut invalide.'], 400);
                }
                $newStatus = $resolved;
            }
        }

        $previousStatus = $order->getStatus();

        $order->setStatus($newStatus);
        $order->setEquipmentLoan($equipmentLoan);
        $order->setEquipmentReturned($equipmentReturned);

        try {
            $entityManager->flush();
        } catch (\Throwable $e) {
            $logger->error('Échec de la mise à jour de la commande staff.', ['error' => $e->getMessage()]);
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de la mise à jour.'], 500);
        }

        // Save menu stats in MongoDB when order is marked as terminée for the first time
        if ($newStatus === OrderStatus::Terminee && $previousStatus !== OrderStatus::Terminee) {
            foreach ($order->getOrderMenus() as $orderMenu) {
                $menu       = $orderMenu->getMenu();
                $quantity   = $orderMenu->getQuantity() ?? 0;
                $unitPrice  = (float) ($orderMenu->getPricePerPerson() ?? '0');
                $totalPrice = number_format($quantity * $unitPrice, 2, '.', '');

                try {
                    $mongoDBService->insertOne('menu_stats', [
                        'order_id'   => $order->getId(),
                        'user_id'    => $order->getUser()->getId(),
                        'menu_id'    => $menu->getId(),
                        'menu_name'  => $menu->getTitle(),
                        'quantity'   => $quantity,
                        'total_price' => $totalPrice,
                        'order_date' => $order->getOrderDate()?->format('Y-m-d H:i:s') ?? '',
                    ]);
                } catch (\Throwable $e) {
                    $logger->error('Échec de l\'enregistrement des stats MongoDB.', [
                        'order_id' => $order->getId(),
                        'error'    => $e->getMessage(),
                    ]);
                }
            }
        }

        $client = $order->getUser();

        try {
            if ($action === 'refuse') {
                $mailService->sendOrderRefused($client, $order, $staffMessage);
            } else {
                $mailService->sendOrderStaffUpdate($client, $order, $staffMessage, $equipmentLoan);
            }
        } catch (\Throwable $e) {
            $logger->error('Échec de l\'envoi du mail staff.', ['error' => $e->getMessage()]);
        }

        return new JsonResponse(['success' => true, 'orderId' => $order->getId()]);
    }

    // =========================================================================
    // CATALOGUE
    // =========================================================================

    /**
     * GET /api/staff/catalog/regimes
     * Retourne tous les régimes (id, label).
     */
    #[Route('/catalog/regimes', name: 'api_staff_catalog_regimes', methods: ['GET'])]
    public function catalogRegimes(RegimeRepository $regimeRepository): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }
        $roles = $user->getRoles();
        if (!in_array('ROLE_STAFF_MEMBER', $roles, true) && !in_array('ROLE_ADMIN', $roles, true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $regimes = $regimeRepository->findBy([], ['label' => 'ASC']);

        return new JsonResponse(array_map(fn($r) => [
            'id'    => $r->getId(),
            'label' => $r->getLabel(),
        ], $regimes));
    }

    /**
     * GET /api/staff/catalog/dishes
     * Retourne tous les plats (id, title, allergenIds[]).
     */
    #[Route('/catalog/dishes', name: 'api_staff_catalog_dishes', methods: ['GET'])]
    public function catalogDishes(DishRepository $dishRepository): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }
        $roles = $user->getRoles();
        if (!in_array('ROLE_STAFF_MEMBER', $roles, true) && !in_array('ROLE_ADMIN', $roles, true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $dishes = $dishRepository->findBy([], ['title' => 'ASC']);

        return new JsonResponse(array_map(fn($d) => [
            'id'          => $d->getId(),
            'title'       => $d->getTitle(),
            'allergenIds' => array_map(fn($a) => $a->getId(), $d->getAllergens()->toArray()),
        ], $dishes));
    }

    /**
     * GET /api/staff/catalog/allergens
     * Retourne tous les allergènes (id, label, dishIds[]).
     */
    #[Route('/catalog/allergens', name: 'api_staff_catalog_allergens', methods: ['GET'])]
    public function catalogAllergens(AllergenRepository $allergenRepository): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }
        $roles = $user->getRoles();
        if (!in_array('ROLE_STAFF_MEMBER', $roles, true) && !in_array('ROLE_ADMIN', $roles, true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $allergens = $allergenRepository->findBy([], ['label' => 'ASC']);

        return new JsonResponse(array_map(fn($a) => [
            'id'      => $a->getId(),
            'label'   => $a->getLabel(),
            'dishIds' => array_map(fn($d) => $d->getId(), $a->getDishes()->toArray()),
        ], $allergens));
    }

    /**
     * GET /api/staff/catalog/menus
     * Retourne tous les menus (id, title, regimeId, dishIds[]).
     */
    #[Route('/catalog/menus', name: 'api_staff_catalog_menus', methods: ['GET'])]
    public function catalogMenus(MenuRepository $menuRepository): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }
        $roles = $user->getRoles();
        if (!in_array('ROLE_STAFF_MEMBER', $roles, true) && !in_array('ROLE_ADMIN', $roles, true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $menus = $menuRepository->findBy([], ['title' => 'ASC']);

        return new JsonResponse(array_map(fn($m) => [
            'id'       => $m->getId(),
            'title'    => $m->getTitle(),
            'regimeId' => $m->getRegime()?->getId(),
            'dishIds'  => array_map(
                fn($md) => $md->getDish()->getId(),
                $m->getMenuDishes()->toArray()
            ),
        ], $menus));
    }

    // =========================================================================
    // CATALOGUE — CRUD MENUS
    // =========================================================================

    /**
     * GET /api/staff/catalog/menus/{id}
     * Retourne le détail complet d'un menu (pour la page édition).
     */
    #[Route('/catalog/menus/{id}', name: 'api_staff_catalog_menu_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function catalogMenuShow(int $id, MenuRepository $menuRepository): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }
        $roles = $user->getRoles();
        if (!in_array('ROLE_STAFF_MEMBER', $roles, true) && !in_array('ROLE_ADMIN', $roles, true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $menu = $menuRepository->find($id);
        if (!$menu) {
            return new JsonResponse(['message' => 'Menu introuvable.'], 404);
        }

        return new JsonResponse([
            'id'                => $menu->getId(),
            'title'             => $menu->getTitle(),
            'description'       => $menu->getDescription(),
            'regimeId'          => $menu->getRegime()?->getId(),
            'pricePerPerson'    => (float) $menu->getPricePerPerson(),
            'minPeople'         => $menu->getMinPeople(),
            'remainingQuantity' => $menu->getRemainingQuantity(),
            'advanceOrderDays'  => $menu->getAdvanceOrderDays(),
            'dishes'            => array_map(fn($md) => [
                'dishId'      => $md->getDish()->getId(),
                'dishTitle'   => $md->getDish()->getTitle(),
                'dishType'    => $md->getDishType()->value,
                'allergenIds' => array_map(fn($a) => $a->getId(), $md->getDish()->getAllergens()->toArray()),
            ], $menu->getMenuDishes()->toArray()),
            'images'            => array_map(fn($img) => [
                'id'        => $img->getId(),
                'imagePath' => $img->getImagePath(),
                'altText'   => $img->getAltText(),
            ], $menu->getImages()->toArray()),
        ]);
    }

    /**
     * POST /api/staff/catalog/menus
     * Crée un nouveau menu avec ses plats et images.
     */
    #[Route('/catalog/menus', name: 'api_staff_catalog_menu_create', methods: ['POST'])]
    public function catalogMenuCreate(
        Request $request,
        RegimeRepository $regimeRepository,
        DishRepository $dishRepository,
        AllergenRepository $allergenRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }
        $roles = $user->getRoles();
        if (!in_array('ROLE_STAFF_MEMBER', $roles, true) && !in_array('ROLE_ADMIN', $roles, true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $title        = trim(strip_tags((string) $request->request->get('title', '')));
        $description  = trim(strip_tags((string) $request->request->get('description', '')));
        $regimeId     = (int) $request->request->get('regimeId', 0);
        $priceRaw     = $request->request->get('pricePerPerson', '');
        $minPeople    = (int) $request->request->get('minPeople', 6);
        $remainingQty = (int) $request->request->get('remainingQuantity', 0);
        $advanceDays  = (int) $request->request->get('advanceOrderDays', 2);
        $dishesJson   = (string) $request->request->get('dishes', '[]');

        if ($title === '' || mb_strlen($title) > 100) {
            return new JsonResponse(['message' => 'Titre invalide (1–100 caractères).'], 400);
        }
        $regime = $regimeRepository->find($regimeId);
        if (!$regime) {
            return new JsonResponse(['message' => 'Régime invalide.'], 400);
        }
        $price = filter_var($priceRaw, FILTER_VALIDATE_FLOAT);
        if ($price === false || $price < 0) {
            return new JsonResponse(['message' => 'Prix par personne invalide.'], 400);
        }
        if ($minPeople < 1) {
            return new JsonResponse(['message' => 'Nombre minimum de personnes invalide.'], 400);
        }

        $menu = new Menu();
        $menu->setTitle($title);
        $menu->setDescription($description !== '' ? $description : null);
        $menu->setRegime($regime);
        $menu->setPricePerPerson((string) round($price, 2));
        $menu->setMinPeople($minPeople);
        $menu->setRemainingQuantity($remainingQty >= 0 ? $remainingQty : 0);
        $menu->setAdvanceOrderDays($advanceDays >= 0 ? $advanceDays : 2);
        $entityManager->persist($menu);

        $dishesData = json_decode($dishesJson, true);
        if (is_array($dishesData)) {
            foreach ($dishesData as $item) {
                $dish = $dishRepository->find((int) ($item['dishId'] ?? 0));
                if (!$dish) continue;
                $dishType = DishType::tryFrom((string) ($item['dishType'] ?? ''));
                if (!$dishType) continue;

                $menuDish = new MenuDish();
                $menuDish->setDish($dish);
                $menuDish->setDishType($dishType);
                $menuDish->setMenu($menu);
                $entityManager->persist($menuDish);

                if (isset($item['allergenIds']) && is_array($item['allergenIds'])) {
                    foreach ($dish->getAllergens()->toArray() as $a) {
                        $dish->removeAllergen($a);
                    }
                    foreach ($item['allergenIds'] as $aId) {
                        $a = $allergenRepository->find((int) $aId);
                        if ($a) $dish->addAllergen($a);
                    }
                }
            }
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/menus/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $allowed = ['image/webp'];
        $files   = $request->files->get('images', []);
        if (!is_array($files)) $files = [$files];
        foreach (array_slice(array_filter($files), 0, 5) as $file) {
            if (!in_array($file->getMimeType(), $allowed, true)) continue;
            $filename    = bin2hex(random_bytes(12)) . '.webp';
            $destination = $uploadDir . $filename;
            // Move to a temp path first, then compress in-place
            $file->move($uploadDir, $filename);
            $this->compressImage($destination, $destination, 1920, 85);
            $img = new MenuImage();
            $img->setImagePath('uploads/menus/' . $filename);
            $img->setAltText($title);
            $img->setMenu($menu);
            $entityManager->persist($img);
        }

        try {
            $entityManager->flush();
        } catch (\Throwable $e) {
            return new JsonResponse(['message' => 'Erreur lors de la création : ' . $e->getMessage()], 500);
        }

        return new JsonResponse(['id' => $menu->getId(), 'title' => $menu->getTitle()], 201);
    }

    /**
     * POST /api/staff/catalog/menus/{id}
     * Modifie un menu existant.
     */
    #[Route('/catalog/menus/{id}', name: 'api_staff_catalog_menu_edit', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function catalogMenuEdit(
        int $id,
        Request $request,
        MenuRepository $menuRepository,
        RegimeRepository $regimeRepository,
        DishRepository $dishRepository,
        AllergenRepository $allergenRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }
        $roles = $user->getRoles();
        if (!in_array('ROLE_STAFF_MEMBER', $roles, true) && !in_array('ROLE_ADMIN', $roles, true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $menu = $menuRepository->find($id);
        if (!$menu) {
            return new JsonResponse(['message' => 'Menu introuvable.'], 404);
        }

        $title          = trim(strip_tags((string) $request->request->get('title', '')));
        $description    = trim(strip_tags((string) $request->request->get('description', '')));
        $regimeId       = (int) $request->request->get('regimeId', 0);
        $priceRaw       = $request->request->get('pricePerPerson', '');
        $minPeople      = (int) $request->request->get('minPeople', 6);
        $remainingQty   = (int) $request->request->get('remainingQuantity', 0);
        $advanceDays    = (int) $request->request->get('advanceOrderDays', 2);
        $dishesJson     = (string) $request->request->get('dishes', '[]');
        $removedIdsJson = (string) $request->request->get('removedImageIds', '[]');

        if ($title === '' || mb_strlen($title) > 100) {
            return new JsonResponse(['message' => 'Titre invalide (1–100 caractères).'], 400);
        }
        $regime = $regimeRepository->find($regimeId);
        if (!$regime) {
            return new JsonResponse(['message' => 'Régime invalide.'], 400);
        }
        $price = filter_var($priceRaw, FILTER_VALIDATE_FLOAT);
        if ($price === false || $price < 0) {
            return new JsonResponse(['message' => 'Prix par personne invalide.'], 400);
        }
        if ($minPeople < 1) {
            return new JsonResponse(['message' => 'Nombre minimum de personnes invalide.'], 400);
        }

        $menu->setTitle($title);
        $menu->setDescription($description !== '' ? $description : null);
        $menu->setRegime($regime);
        $menu->setPricePerPerson((string) round($price, 2));
        $menu->setMinPeople($minPeople);
        $menu->setRemainingQuantity($remainingQty >= 0 ? $remainingQty : 0);
        $menu->setAdvanceOrderDays($advanceDays >= 0 ? $advanceDays : 2);

        // Suppression des anciens plats — flush immédiat pour éviter la violation
        // de la contrainte unique (menu_id, dish_id) lors du re-ajout
        foreach ($menu->getMenuDishes()->toArray() as $md) {
            $entityManager->remove($md);
        }
        $entityManager->flush();

        $dishesData = json_decode($dishesJson, true);
        if (is_array($dishesData)) {
            foreach ($dishesData as $item) {
                $dish = $dishRepository->find((int) ($item['dishId'] ?? 0));
                if (!$dish) continue;
                $dishType = DishType::tryFrom((string) ($item['dishType'] ?? ''));
                if (!$dishType) continue;

                $menuDish = new MenuDish();
                $menuDish->setDish($dish);
                $menuDish->setDishType($dishType);
                $menuDish->setMenu($menu);
                $entityManager->persist($menuDish);

                if (isset($item['allergenIds']) && is_array($item['allergenIds'])) {
                    foreach ($dish->getAllergens()->toArray() as $a) {
                        $dish->removeAllergen($a);
                    }
                    foreach ($item['allergenIds'] as $aId) {
                        $a = $allergenRepository->find((int) $aId);
                        if ($a) $dish->addAllergen($a);
                    }
                }
            }
        }

        // Suppression des images demandées
        $removedIds    = json_decode($removedIdsJson, true);
        $removedIds    = is_array($removedIds) ? array_map('intval', $removedIds) : [];
        $publicDir     = $this->getParameter('kernel.project_dir') . '/public/';
        $pathsToDelete = [];

        // Snapshot BEFORE any remove() — needed for correct $keptCount below
        $allImages = $menu->getImages()->toArray();

        foreach ($allImages as $img) {
            if (in_array($img->getId(), $removedIds, true)) {
                // Collect path — file deletion happens after successful flush
                $pathsToDelete[] = $publicDir . ltrim($img->getImagePath(), '/');
                $entityManager->remove($img);
            }
        }

        // Ajout de nouvelles images (plafond à 5)
        $keptCount = count($allImages) - count($removedIds);
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/menus/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $allowed = ['image/webp'];
        $files   = $request->files->get('images', []);
        if (!is_array($files)) $files = [$files];
        foreach (array_slice(array_filter($files), 0, max(0, 5 - $keptCount)) as $file) {
            if (!in_array($file->getMimeType(), $allowed, true)) continue;
            $filename    = bin2hex(random_bytes(12)) . '.webp';
            $destination = $uploadDir . $filename;
            $file->move($uploadDir, $filename);
            $this->compressImage($destination, $destination, 1920, 85);
            $img = new MenuImage();
            $img->setImagePath('uploads/menus/' . $filename);
            $img->setAltText($title);
            $img->setMenu($menu);
            $entityManager->persist($img);
        }

        try {
            $entityManager->flush();
        } catch (\Throwable $e) {
            return new JsonResponse(['message' => 'Erreur lors de la modification : ' . $e->getMessage()], 500);
        }

        // Suppression physique des fichiers — uniquement après flush réussi
        foreach ($pathsToDelete as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        return new JsonResponse(['id' => $menu->getId(), 'title' => $menu->getTitle()]);
    }

    /**
     * DELETE /api/staff/catalog/menus/{id}
     * Supprime un menu existant et ses fichiers images du disque.
     */
    #[Route('/catalog/menus/{id}', name: 'api_staff_catalog_menu_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function catalogMenuDelete(
        int $id,
        MenuRepository $menuRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }
        $roles = $user->getRoles();
        if (!in_array('ROLE_STAFF_MEMBER', $roles, true) && !in_array('ROLE_ADMIN', $roles, true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $menu = $menuRepository->find($id);
        if (!$menu) {
            return new JsonResponse(['message' => 'Menu introuvable.'], 404);
        }

        // Suppression des fichiers images du disque
        $publicDir = $this->getParameter('kernel.project_dir') . '/public/';
        foreach ($menu->getImages()->toArray() as $img) {
            $path = $publicDir . $img->getImagePath();
            if (is_file($path)) {
                @unlink($path);
            }
        }

        try {
            $entityManager->remove($menu);
            $entityManager->flush();
        } catch (\Throwable) {
            return new JsonResponse(['message' => 'Erreur lors de la suppression du menu.'], 500);
        }

        return new JsonResponse(['message' => 'Menu supprimé avec succès.']);
    }

    private function compressImage(string $source, string $destination, int $maxWidth = 1920, int $quality = 85): bool
    {
        if (!function_exists('imagecreatefromwebp')) {
            return false;
        }
        $oldLimit = ini_get('memory_limit');
        ini_set('memory_limit', '512M');

        $src = @imagecreatefromwebp($source);
        if (!$src) {
            ini_set('memory_limit', $oldLimit);
            return false;
        }

        $width  = imagesx($src);
        $height = imagesy($src);

        if ($width > $maxWidth) {
            $newWidth  = $maxWidth;
            $newHeight = (int) round(($height / $width) * $maxWidth);
        } else {
            $newWidth  = $width;
            $newHeight = $height;
        }

        $dst = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        unset($src);

        $ok = imagewebp($dst, $destination, $quality);
        unset($dst);
        gc_collect_cycles();
        ini_set('memory_limit', $oldLimit);

        return $ok;
    }
}
