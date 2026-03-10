<?php

namespace App\Controller;

use App\Service\MongoDBService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class MongoController extends AbstractController
{
    public function __construct(private MongoDBService $mongo) {}

    #[Route('/api/mongo/{collection}', name: 'api_mongo_collection', methods: ['GET'])]
    public function getCollection(string $collection): JsonResponse
    {
        $allowed = ['reviews', 'menu_stats', 'order_status_history'];
        if (!in_array($collection, $allowed, true)) {
            return $this->json(['error' => 'Collection not allowed'], 400);
        }

        $data = $this->mongo->findAll($collection);
        return $this->json($data);
    }
}
