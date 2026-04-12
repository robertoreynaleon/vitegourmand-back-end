<?php

namespace App\Service;

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

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

    public function insertOne(string $collection, array $data): string
    {
        $allowed = ['reviews', 'menu_stats', 'order_status_history'];
        if (!in_array($collection, $allowed, true)) {
            throw new \InvalidArgumentException('Collection not allowed.');
        }

        $result = $this->client
            ->selectDatabase($this->dbName)
            ->selectCollection($collection)
            ->insertOne($data);

        return (string) $result->getInsertedId();
    }

    public function findByField(string $collection, string $field, mixed $value): array
    {
        $allowed = ['reviews', 'menu_stats', 'order_status_history'];
        if (!in_array($collection, $allowed, true)) {
            return [];
        }

        $coll   = $this->client->selectDatabase($this->dbName)->selectCollection($collection);
        $cursor = $coll->find([$field => $value], ['sort' => ['created_at' => -1]]);

        $results = [];
        foreach ($cursor as $doc) {
            $row        = (array) $doc;
            $row['_id'] = (string) $doc['_id'];
            $results[]  = $row;
        }

        return $results;
    }

    public function findOneById(string $collection, string $id): ?array
    {
        $allowed = ['reviews', 'menu_stats', 'order_status_history'];
        if (!in_array($collection, $allowed, true)) {
            return null;
        }

        $doc = $this->client
            ->selectDatabase($this->dbName)
            ->selectCollection($collection)
            ->findOne(['_id' => new ObjectId($id)]);

        if (!$doc) {
            return null;
        }

        $row        = (array) $doc;
        $row['_id'] = (string) $doc['_id'];

        return $row;
    }

    public function findByStatus(string $collection, string $status): array
    {
        $allowed = ['reviews', 'menu_stats', 'order_status_history'];
        if (!in_array($collection, $allowed, true)) {
            return [];
        }

        $coll   = $this->client->selectDatabase($this->dbName)->selectCollection($collection);
        $cursor = $coll->find(['status' => $status], ['sort' => ['created_at' => -1]]);

        $results = [];
        foreach ($cursor as $doc) {
            $row        = (array) $doc;
            $row['_id'] = (string) $doc['_id'];
            $results[]  = $row;
        }

        return $results;
    }

    public function updateOneById(string $collection, string $id, array $update): bool
    {
        $allowed = ['reviews', 'menu_stats', 'order_status_history'];
        if (!in_array($collection, $allowed, true)) {
            return false;
        }

        $result = $this->client
            ->selectDatabase($this->dbName)
            ->selectCollection($collection)
            ->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => $update]
            );

        return $result->getMatchedCount() > 0;
    }
}
