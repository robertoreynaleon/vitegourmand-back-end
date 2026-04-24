<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur du panier.
 * Valide les articles côté serveur avant que le client ne les stocke dans localStorage.
 */
#[Route('/api/cart')]
class CartController extends AbstractController
{
    /**
     * POST /api/cart/add
     *
     * Valide que le menu existe en base et retourne ses données enrichies
     * (prix, minimum de personnes, délai) pour que le client puisse les
     * persister dans son localStorage.
     *
     * Route publique : l'utilisateur peut ajouter au panier avant connexion.
     */
    #[Route('/add', name: 'api_cart_add', methods: ['POST'])]
    public function add(Request $request, MenuRepository $menuRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $menuId  = (int) ($data['menuId']   ?? 0);
        $quantity = (int) ($data['quantity'] ?? 0);

        if ($menuId <= 0) {
            return new JsonResponse(['message' => 'Identifiant de menu invalide.'], 400);
        }

        $menu = $menuRepository->find($menuId);
        if (!$menu) {
            return new JsonResponse(['message' => 'Menu introuvable.'], 404);
        }

        $minPeople = $menu->getMinPeople();

        // Correction silencieuse si la quantité est inférieure au minimum
        if ($quantity < $minPeople) {
            $quantity = $minPeople;
        }

        return new JsonResponse([
            'success' => true,
            'item'    => [
                'menuId'          => $menu->getId(),
                'menuTitle'       => $menu->getTitle(),
                'pricePerPerson'  => (float) $menu->getPricePerPerson(),
                'minPeople'       => $minPeople,
                'advanceOrderDays' => $menu->getAdvanceOrderDays(),
                'quantity'        => $quantity,
            ],
        ]);
    }
}
