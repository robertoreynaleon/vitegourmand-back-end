<?php

namespace App\Controller;

use App\Service\MongoDBService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur d'accès public aux collections MongoDB.
 * Expose en lecture seule uniquement les collections publiques (avis, stats, historique statuts).
 * La collection contact_messages est volontairement exclue (accès staff uniquement).
 */
class MongoController extends AbstractController
{
    public function __construct(private MongoDBService $mongo) {}

    /**
     * GET /api/mongo/{collection}
     *
     * Retourne tous les documents d'une collection MongoDB publique.
     * Collections autorisées : reviews, menu_stats, order_status_history.
     */
    #[Route('/api/mongo/{collection}', name: 'api_mongo_collection', methods: ['GET'])]
    public function getCollection(string $collection): JsonResponse
    {
        // Liste blanche des collections accessibles publiquement
        $allowed = ['reviews', 'menu_stats', 'order_status_history'];
        if (!in_array($collection, $allowed, true)) {
            return $this->json(['error' => 'Collection non autorisée.'], 400);
        }

        $data = $this->mongo->findAll($collection);
        return $this->json($data);
    }
}
