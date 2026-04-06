<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
}
