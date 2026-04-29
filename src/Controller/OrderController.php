<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderMenu;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Repository\MenuRepository;
use App\Repository\OrderRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur de gestion des commandes côté utilisateur.
 * Permet de créer, consulter, modifier et annuler une commande.
 * Toutes les routes nécessitent un JWT valide.
 */
#[Route('/api/orders')]
class OrderController extends AbstractController
{
    /**
     * POST /api/orders/create
     *
     * Crée une commande avec ses lignes (order_menus) en base.
     * Utilise /create pour éviter le conflit avec la route API Platform POST /api/orders.
     * L'utilisateur est récupéré depuis le token JWT.
     */
    #[Route('/create', name: 'api_order_create', methods: ['POST'])]
    public function create(
        Request $request,
        MenuRepository $menuRepository,
        EntityManagerInterface $entityManager,
        MailService $mailService,
        LoggerInterface $logger
    ): JsonResponse {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }

        // Seuls les clients peuvent passer commande — double vérification (défense en profondeur)
        if (!in_array('ROLE_CLIENT', $user->getRoles(), true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $orderDateStr    = trim((string) ($data['orderDate']       ?? ''));
        $deliveryDateStr = trim((string) ($data['deliveryDate']    ?? ''));
        $deliveryTimeStr = trim((string) ($data['deliveryTime']    ?? ''));
        $deliveryAddress = trim((string) ($data['deliveryAddress'] ?? ''));
        // deliveryFee dépend de la distance de livraison : valeur côté client acceptée
        $rawDeliveryFee  = max(0.0, (float) ($data['deliveryFee']  ?? 0));
        $equipmentLoan   = (bool)  ($data['equipmentLoan']         ?? false);
        $items           = (array) ($data['items']                 ?? []);

        if (!$deliveryDateStr || !$deliveryTimeStr || !$deliveryAddress || empty($items)) {
            return new JsonResponse(['message' => 'Données de commande incomplètes.'], 400);
        }

        try {
            $deliveryDate = new \DateTime($deliveryDateStr);
        } catch (\Throwable) {
            return new JsonResponse(['message' => 'Date de livraison invalide.'], 400);
        }

        try {
            $deliveryTime = new \DateTime($deliveryTimeStr);
        } catch (\Throwable) {
            return new JsonResponse(['message' => 'Heure de livraison invalide.'], 400);
        }

        $order = new Order();
        $order->setUser($user);
        if ($orderDateStr) {
            try {
                $parsedDate = new \DateTime($orderDateStr);
                $parsedDate->setTimezone(new \DateTimeZone('Europe/Paris'));
                $order->setOrderDate($parsedDate);
            } catch (\Throwable) {
                // Date invalide : on garde la date par défaut du constructeur
            }
        }
        // Calcul des totaux côté serveur : les prix ne sont jamais lus depuis le payload client
        ['subtotal' => $subtotal, 'orderMenus' => $orderMenus] =
            $this->computeOrderTotals($items, $menuRepository, $logger);

        $totalAmount = round($subtotal + $rawDeliveryFee, 2);

        // Vérification du stock disponible avant toute persistance
        foreach ($orderMenus as ['menu' => $menu, 'quantity' => $quantity]) {
            $available = $menu->getRemainingQuantity();
            if ($available !== null && $available < $quantity) {
                return new JsonResponse([
                    'success'    => false,
                    'stockError' => true,
                    'message'    => sprintf(
                        'Stock insuffisant pour le menu « %s » : %d place(s) disponible(s), %d demandée(s). Veuillez réduire la quantité ou choisir un autre menu.',
                        $menu->getTitle(),
                        $available,
                        $quantity
                    ),
                ], 422);
            }
        }

        $order->setDeliveryDate($deliveryDate);
        $order->setDeliveryTime($deliveryTime);
        $order->setDeliveryAddress(strip_tags($deliveryAddress));
        $order->setSubtotal((string) $subtotal);
        $order->setDeliveryFee((string) round($rawDeliveryFee, 2));
        $order->setTotalAmount((string) $totalAmount);
        $order->setEquipmentLoan($equipmentLoan);
        $order->setEquipmentReturned(false);
        $order->setStatus(OrderStatus::EnAttente);

        $entityManager->persist($order);

        foreach ($orderMenus as ['menu' => $menu, 'quantity' => $quantity, 'price' => $price]) {
            // Décrémentation du stock
            if ($menu->getRemainingQuantity() !== null) {
                $menu->setRemainingQuantity($menu->getRemainingQuantity() - $quantity);
            }

            $orderMenu = new OrderMenu();
            $orderMenu->setOrder($order);
            $orderMenu->setMenu($menu);
            $orderMenu->setQuantity($quantity);
            $orderMenu->setPricePerPerson((string) $price);
            $entityManager->persist($orderMenu);
        }

        try {
            $entityManager->flush();
        } catch (\Throwable $e) {
            $logger->error('Échec de l\'enregistrement de la commande.', ['error' => $e->getMessage()]);
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de l\'enregistrement de la commande.'], 500);
        }

        try {
            $mailService->sendOrderConfirmation($user, $order);
        } catch (\Throwable $e) {
            $logger->error('Échec de l\'envoi du mail de confirmation.', ['error' => $e->getMessage()]);
        }

        return new JsonResponse([
            'success' => true,
            'orderId' => $order->getId(),
        ], 201);
    }

    /**
     * GET /api/orders/{id}/detail
     *
     * Retourne le détail d'une commande appartenant à l'utilisateur connecté.
     * La route /{id} est évitée car API Platform l'intercepte pour GET item.
     */
    #[Route('/{id}/detail', name: 'api_order_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, OrderRepository $orderRepository): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }

        // Seuls les clients peuvent consulter leurs commandes
        if (!in_array('ROLE_CLIENT', $user->getRoles(), true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $order = $orderRepository->find($id);
        if (!$order || $order->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['message' => 'Commande introuvable.'], 404);
        }

        $items = array_map(fn(OrderMenu $om) => [
            'menuId'        => $om->getMenu()->getId(),
            'menuTitle'     => $om->getMenu()->getTitle(),
            'pricePerPerson' => (float) $om->getPricePerPerson(),
            'minPeople'     => $om->getMenu()->getMinPeople(),
            'advanceOrderDays' => $om->getMenu()->getAdvanceOrderDays(),
            'quantity'      => $om->getQuantity(),
        ], $order->getOrderMenus()->toArray());

        return new JsonResponse([
            'id'              => $order->getId(),
            'orderDate'       => $order->getOrderDate()?->format('d/m/Y \à H:i'),
            'deliveryDate'    => $order->getDeliveryDate()?->format('Y-m-d'),
            'deliveryTime'    => $order->getDeliveryTime()?->format('H:i'),
            'deliveryAddress' => $order->getDeliveryAddress(),
            'subtotal'        => $order->getSubtotal(),
            'deliveryFee'     => $order->getDeliveryFee(),
            'totalAmount'     => $order->getTotalAmount(),
            'status'          => $order->getStatus()->value,
            'items'           => $items,
        ]);
    }

    /**
     * PUT /api/orders/{id}/edit
     *
     * Modifie une commande en attente appartenant à l'utilisateur connecté.
     */
    #[Route('/{id}/edit', name: 'api_order_edit', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function edit(
        int $id,
        Request $request,
        OrderRepository $orderRepository,
        MenuRepository $menuRepository,
        EntityManagerInterface $entityManager,
        MailService $mailService,
        LoggerInterface $logger
    ): JsonResponse {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }

        // Seuls les clients peuvent modifier leurs commandes
        if (!in_array('ROLE_CLIENT', $user->getRoles(), true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $order = $orderRepository->find($id);
        if (!$order || $order->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['message' => 'Commande introuvable.'], 404);
        }

        if ($order->getStatus() !== OrderStatus::EnAttente) {
            return new JsonResponse(['message' => 'Cette commande ne peut plus être modifiée.'], 403);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $deliveryDateStr = trim((string) ($data['deliveryDate']    ?? ''));
        $deliveryTimeStr = trim((string) ($data['deliveryTime']    ?? ''));
        $deliveryAddress = trim((string) ($data['deliveryAddress'] ?? ''));
        // deliveryFee dépend de la distance de livraison : valeur côté client acceptée
        $rawDeliveryFee  = max(0.0, (float) ($data['deliveryFee']  ?? 0));
        $items           = (array) ($data['items']                 ?? []);

        if (!$deliveryDateStr || !$deliveryTimeStr || !$deliveryAddress || empty($items)) {
            return new JsonResponse(['message' => 'Données de commande incomplètes.'], 400);
        }

        try {
            $deliveryDate = new \DateTime($deliveryDateStr);
        } catch (\Throwable) {
            return new JsonResponse(['message' => 'Date de livraison invalide.'], 400);
        }

        try {
            $deliveryTime = new \DateTime($deliveryTimeStr);
        } catch (\Throwable) {
            return new JsonResponse(['message' => 'Heure de livraison invalide.'], 400);
        }

        // Calcul des totaux côté serveur : les prix ne sont jamais lus depuis le payload client
        ['subtotal' => $subtotal, 'orderMenus' => $orderMenus] =
            $this->computeOrderTotals($items, $menuRepository, $logger);

        $totalAmount = round($subtotal + $rawDeliveryFee, 2);

        // Vérification du stock disponible pour les nouvelles quantités.
        // On tient compte du fait que l'ancienne réservation va être libérée :
        // on calcule le stock effectif = remainingQuantity + ancienne quantité pour ce menu.
        $oldQuantityByMenuId = [];
        foreach ($order->getOrderMenus() as $existing) {
            $oldQuantityByMenuId[$existing->getMenu()->getId()] =
                ($oldQuantityByMenuId[$existing->getMenu()->getId()] ?? 0) + $existing->getQuantity();
        }

        foreach ($orderMenus as ['menu' => $menu, 'quantity' => $quantity]) {
            $available = $menu->getRemainingQuantity();
            if ($available !== null) {
                $restored  = $oldQuantityByMenuId[$menu->getId()] ?? 0;
                $effective = $available + $restored;
                if ($effective < $quantity) {
                    return new JsonResponse([
                        'success'    => false,
                        'stockError' => true,
                        'message'    => sprintf(
                            'Stock insuffisant pour le menu « %s » : %d place(s) disponible(s), %d demandée(s). Veuillez réduire la quantité ou choisir un autre menu.',
                            $menu->getTitle(),
                            $effective,
                            $quantity
                        ),
                    ], 422);
                }
            }
        }

        $order->setDeliveryDate($deliveryDate);
        $order->setDeliveryTime($deliveryTime);
        $order->setDeliveryAddress(strip_tags($deliveryAddress));
        $order->setSubtotal((string) $subtotal);
        $order->setDeliveryFee((string) round($rawDeliveryFee, 2));
        $order->setTotalAmount((string) $totalAmount);

        // Étape 1 : restitution du stock des anciennes lignes + suppression
        foreach ($order->getOrderMenus() as $existing) {
            $existingMenu = $existing->getMenu();
            if ($existingMenu->getRemainingQuantity() !== null) {
                $existingMenu->setRemainingQuantity(
                    $existingMenu->getRemainingQuantity() + $existing->getQuantity()
                );
            }
            $entityManager->remove($existing);
        }
        $entityManager->flush();

        // Étape 2 : recréation des nouvelles lignes avec décrémentation du stock
        foreach ($orderMenus as ['menu' => $menu, 'quantity' => $quantity, 'price' => $price]) {
            if ($menu->getRemainingQuantity() !== null) {
                $menu->setRemainingQuantity($menu->getRemainingQuantity() - $quantity);
            }

            $orderMenu = new OrderMenu();
            $orderMenu->setOrder($order);
            $orderMenu->setMenu($menu);
            $orderMenu->setQuantity($quantity);
            $orderMenu->setPricePerPerson((string) $price);
            $entityManager->persist($orderMenu);
        }

        try {
            $entityManager->flush();
        } catch (\Throwable $e) {
            $logger->error('Échec de la modification de la commande.', ['error' => $e->getMessage()]);
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de la modification.'], 500);
        }

        try {
            $mailService->sendOrderModified($user, $order);
        } catch (\Throwable $e) {
            $logger->error('Échec de l\'envoi du mail de modification.', ['error' => $e->getMessage()]);
        }

        return new JsonResponse(['success' => true, 'orderId' => $order->getId()]);
    }

    /**
     * DELETE /api/orders/{id}/cancel
     *
     * Annule (supprime) une commande en attente appartenant à l'utilisateur connecté.
     */
    #[Route('/{id}/cancel', name: 'api_order_cancel', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function cancel(
        int $id,
        OrderRepository $orderRepository,
        EntityManagerInterface $entityManager,
        MailService $mailService,
        LoggerInterface $logger
    ): JsonResponse {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }

        // Seuls les clients peuvent annuler leurs commandes
        if (!in_array('ROLE_CLIENT', $user->getRoles(), true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $order = $orderRepository->find($id);
        if (!$order || $order->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['message' => 'Commande introuvable.'], 404);
        }

        if ($order->getStatus() !== OrderStatus::EnAttente) {
            return new JsonResponse(['message' => 'Cette commande ne peut plus être annulée.'], 403);
        }

        $orderId        = $order->getId();
        $orderDateFormatted = $order->getOrderDate()?->format('d/m/Y \à H:i') ?? '';

        // Restitution du stock avant suppression (cascade supprime les orderMenus)
        foreach ($order->getOrderMenus() as $orderMenu) {
            $menu = $orderMenu->getMenu();
            if ($menu->getRemainingQuantity() !== null) {
                $menu->setRemainingQuantity($menu->getRemainingQuantity() + $orderMenu->getQuantity());
            }
        }

        try {
            $entityManager->remove($order);
            $entityManager->flush();
        } catch (\Throwable $e) {
            $logger->error('Échec de l\'annulation de la commande.', ['error' => $e->getMessage()]);
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de l\'annulation.'], 500);
        }

        try {
            $mailService->sendOrderCancelled($user, $orderId, $orderDateFormatted);
        } catch (\Throwable $e) {
            $logger->error('Échec de l\'envoi du mail d\'annulation.', ['error' => $e->getMessage()]);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * Calcule les totaux d'une commande côté serveur à partir des menuId et quantités.
     *
     * Le prix unitaire est systématiquement lu depuis la base de données (Menu::pricePerPerson),
     * jamais depuis le payload client. Cela empêche un client de falsifier les prix.
     *
     * Logique de remise : si la quantité dépasse minPeople + 5, une remise de 10 % est appliquée
     * (même règle que dans cartCalc.ts côté frontend, mais vérifiée ici côté serveur).
     *
     * @param array<int, array<string, mixed>> $items          Lignes du payload client [{menuId, quantity}, ...]
     * @param MenuRepository                   $menuRepository Repository pour charger les menus depuis la BDD
     * @param LoggerInterface                  $logger         Logger pour signaler les menus introuvables
     * @return array{subtotal: float, orderMenus: list<array{menu: \App\Entity\Menu, quantity: int, price: float}>}
     */
    private function computeOrderTotals(
        array $items,
        MenuRepository $menuRepository,
        LoggerInterface $logger
    ): array {
        $subtotal   = 0.0;
        $orderMenus = [];

        foreach ($items as $item) {
            $menuId   = (int) ($item['menuId']   ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($menuId <= 0 || $quantity <= 0) {
                continue;
            }

            $menu = $menuRepository->find($menuId);
            if (!$menu) {
                $logger->warning('Menu introuvable lors du calcul de la commande.', ['menuId' => $menuId]);
                continue;
            }

            // Prix unitaire lu depuis la BDD — le client ne peut pas l'influencer
            $unitPrice = (float) $menu->getPricePerPerson();
            $minPeople = $menu->getMinPeople();

            // Application de la remise de 10 % si la quantité dépasse minPeople + 5
            if ($quantity > $minPeople + 5) {
                $unitPrice = round($unitPrice * 0.9, 2);
            }

            $lineTotal  = round($unitPrice * $quantity, 2);
            $subtotal  += $lineTotal;

            $orderMenus[] = [
                'menu'     => $menu,
                'quantity' => $quantity,
                'price'    => $unitPrice,
            ];
        }

        return [
            'subtotal'   => round($subtotal, 2),
            'orderMenus' => $orderMenus,
        ];
    }
}
