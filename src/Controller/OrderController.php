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
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $orderDateStr    = trim((string) ($data['orderDate']       ?? ''));
        $deliveryDateStr = trim((string) ($data['deliveryDate']    ?? ''));
        $deliveryTimeStr = trim((string) ($data['deliveryTime']    ?? ''));
        $deliveryAddress = trim((string) ($data['deliveryAddress'] ?? ''));
        $rawSubtotal     = (float) ($data['subtotal']              ?? 0);
        $rawDeliveryFee  = (float) ($data['deliveryFee']           ?? 0);
        $rawTotalAmount  = (float) ($data['totalAmount']           ?? 0);
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
                // garde la date du constructeur en fallback
            }
        }
        $order->setDeliveryDate($deliveryDate);
        $order->setDeliveryTime($deliveryTime);
        $order->setDeliveryAddress(strip_tags($deliveryAddress));
        $order->setSubtotal((string) round($rawSubtotal, 2));
        $order->setDeliveryFee((string) round($rawDeliveryFee, 2));
        $order->setTotalAmount((string) round($rawTotalAmount, 2));
        $order->setEquipmentLoan($equipmentLoan);
        $order->setEquipmentReturned(false);
        $order->setStatus(OrderStatus::EnAttente);

        $entityManager->persist($order);

        foreach ($items as $item) {
            $menuId   = (int)   ($item['menuId']         ?? 0);
            $quantity = (int)   ($item['quantity']       ?? 0);
            $price    = (float) ($item['pricePerPerson'] ?? 0);

            if ($menuId <= 0 || $quantity <= 0) {
                continue;
            }

            $menu = $menuRepository->find($menuId);
            if (!$menu) {
                $logger->warning('Menu introuvable lors de la création de commande.', ['menuId' => $menuId]);
                continue;
            }

            $orderMenu = new OrderMenu();
            $orderMenu->setOrder($order);
            $orderMenu->setMenu($menu);
            $orderMenu->setQuantity($quantity);
            $orderMenu->setPricePerPerson((string) round($price, 2));

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
        $rawSubtotal     = (float) ($data['subtotal']              ?? 0);
        $rawDeliveryFee  = (float) ($data['deliveryFee']           ?? 0);
        $rawTotalAmount  = (float) ($data['totalAmount']           ?? 0);
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

        $order->setDeliveryDate($deliveryDate);
        $order->setDeliveryTime($deliveryTime);
        $order->setDeliveryAddress(strip_tags($deliveryAddress));
        $order->setSubtotal((string) round($rawSubtotal, 2));
        $order->setDeliveryFee((string) round($rawDeliveryFee, 2));
        $order->setTotalAmount((string) round($rawTotalAmount, 2));

        // Supprimer les anciennes lignes
        foreach ($order->getOrderMenus() as $existing) {
            $entityManager->remove($existing);
        }
        $entityManager->flush();

        // Recréer les lignes
        foreach ($items as $item) {
            $menuId   = (int)   ($item['menuId']         ?? 0);
            $quantity = (int)   ($item['quantity']       ?? 0);
            $price    = (float) ($item['pricePerPerson'] ?? 0);

            if ($menuId <= 0 || $quantity <= 0) {
                continue;
            }

            $menu = $menuRepository->find($menuId);
            if (!$menu) {
                $logger->warning('Menu introuvable lors de la modification de commande.', ['menuId' => $menuId]);
                continue;
            }

            $orderMenu = new OrderMenu();
            $orderMenu->setOrder($order);
            $orderMenu->setMenu($menu);
            $orderMenu->setQuantity($quantity);
            $orderMenu->setPricePerPerson((string) round($price, 2));
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

        $order = $orderRepository->find($id);
        if (!$order || $order->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['message' => 'Commande introuvable.'], 404);
        }

        if ($order->getStatus() !== OrderStatus::EnAttente) {
            return new JsonResponse(['message' => 'Cette commande ne peut plus être annulée.'], 403);
        }

        $orderId        = $order->getId();
        $orderDateFormatted = $order->getOrderDate()?->format('d/m/Y \à H:i') ?? '';

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
}
