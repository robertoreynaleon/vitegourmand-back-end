<?php

namespace App\Service;

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

class MongoDBService
{
    private Client $client;
    private string $dbName;

    private const ALLOWED = ['reviews', 'menu_stats', 'order_status_history', 'contact_messages'];

    public function __construct(string $mongodbUrl, string $mongodbDatabase)
    {
        $this->client = new Client($mongodbUrl);
        $this->dbName = $mongodbDatabase;
    }

    public function findAll(string $collection, int $limit = 100): array
    {
        if (!in_array($collection, self::ALLOWED, true)) {
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
        if (!in_array($collection, self::ALLOWED, true)) {
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
        if (!in_array($collection, self::ALLOWED, true)) {
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
        if (!in_array($collection, self::ALLOWED, true)) {
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
        if (!in_array($collection, self::ALLOWED, true)) {
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
        if (!in_array($collection, self::ALLOWED, true)) {
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
        if (!in_array($collection, self::ALLOWED, true)) {
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

    /**
     * Finds documents matching optional filters (menu_id, date_from, date_to).
     * Date fields are compared as strings in "Y-m-d H:i:s" format.
     *
     * @param array{menu_id?: int, date_from?: string, date_to?: string} $filters
     */
    public function findByFilters(string $collection, array $filters = []): array
    {
        if (!in_array($collection, self::ALLOWED, true)) {
            return [];
        }

        $query = [];

        if (!empty($filters['menu_id'])) {
            $query['menu_id'] = (int) $filters['menu_id'];
        }

        $dateFilter = [];
        if (!empty($filters['date_from'])) {
            $dateFilter['$gte'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $dateFilter['$lte'] = $filters['date_to'];
        }
        if (!empty($dateFilter)) {
            $query['order_date'] = $dateFilter;
        }

        $coll   = $this->client->selectDatabase($this->dbName)->selectCollection($collection);
        $cursor = $coll->find($query, ['sort' => ['order_date' => -1]]);

        $results = [];
        foreach ($cursor as $doc) {
            $row        = (array) $doc;
            $row['_id'] = (string) $doc['_id'];
            $results[]  = $row;
        }

        return $results;
    }

    /**
     * Counts documents matching a field/value pair.
     */
    public function countByField(string $collection, string $field, mixed $value): int
    {
        if (!in_array($collection, self::ALLOWED, true)) {
            return 0;
        }

        return (int) $this->client
            ->selectDatabase($this->dbName)
            ->selectCollection($collection)
            ->countDocuments([$field => $value]);
    }
}
