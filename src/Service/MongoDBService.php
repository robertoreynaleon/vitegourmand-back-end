<?php

namespace App\Service;

use MongoDB\Client;

class MongoDBService
{
    private Client $client;
    private string $dbName;

    public function __construct(string $mongodbUrl, string $mongodbDatabase)
    {
        $this->client = new Client($mongodbUrl);
        $this->dbName = $mongodbDatabase;
    }

    public function findAll(string $collection, int $limit = 100): array
    {
        $allowed = ['reviews', 'menu_stats', 'order_status_history'];
        if (!in_array($collection, $allowed, true)) {
            return [];
        }

        $coll   = $this->client->selectDatabase($this->dbName)->selectCollection($collection);
        $cursor = $coll->find([], ['limit' => $limit]);

        $results = [];
        foreach ($cursor as $doc) {
            $row        = (array) $doc;
            $row['_id'] = (string) $doc['_id'];
            $results[]  = $row;
        }

        return $results;
    }

    public function deleteByField(string $collection, string $field, mixed $value): int
    {
        $allowed = ['reviews', 'menu_stats', 'order_status_history'];
        if (!in_array($collection, $allowed, true)) {
            return 0;
        }

        $result = $this->client
            ->selectDatabase($this->dbName)
            ->selectCollection($collection)
            ->deleteMany([$field => $value]);

        return $result->getDeletedCount();
    }
}
