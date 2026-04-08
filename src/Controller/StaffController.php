<?php

namespace App\Controller;

use App\Entity\OrderMenu;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use App\Service\MailService;
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
        LoggerInterface $logger
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

        $order->setStatus($newStatus);
        $order->setEquipmentLoan($equipmentLoan);
        $order->setEquipmentReturned($equipmentReturned);

        try {
            $entityManager->flush();
        } catch (\Throwable $e) {
            $logger->error('Échec de la mise à jour de la commande staff.', ['error' => $e->getMessage()]);
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de la mise à jour.'], 500);
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
}
