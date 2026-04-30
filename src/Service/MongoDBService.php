<?php

namespace App\Service;

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

/**
 * Service d'accès à la base de données MongoDB.
 * Fournit des méthodes génériques (find, insert, update, delete, count)
 * sur une liste de collections autorisées (liste blanche de sécurité).
 * Utilisé par les contrôleurs pour les données non relationnelles :
 * avis, stats menus, historique commandes, messages de contact.
 */
class MongoDBService
{
    private Client $client;
    private string $dbName;

    /** Collections MongoDB accessibles via ce service. */
    private const ALLOWED = ['reviews', 'menu_stats', 'contact_messages'];

    /**
     * Initialise la connexion MongoDB avec l'URL et le nom de base fournis
     * (injectés depuis la configuration Symfony via services.yaml).
     */
    public function __construct(string $mongodbUrl, string $mongodbDatabase)
    {
        $this->client = new Client($mongodbUrl);
        $this->dbName = $mongodbDatabase;
    }

    /**
     * Retourne tous les documents d'une collection (jusqu'à $limit résultats).
     * L'_id MongoDB est converti en chaîne de caractères pour la sérialisation JSON.
     */
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

    /**
     * Supprime tous les documents d'une collection où le champ $field vaut $value.
     * Retourne le nombre de documents supprimés.
     */
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

    /**
     * Insère un document dans la collection et retourne son _id sous forme de chaîne.
     * Lève une exception si la collection n'est pas autorisée.
     */
    public function insertOne(string $collection, array $data): string
    {
        if (!in_array($collection, self::ALLOWED, true)) {
            throw new \InvalidArgumentException('Collection non autorisée.');
        }

        $result = $this->client
            ->selectDatabase($this->dbName)
            ->selectCollection($collection)
            ->insertOne($data);

        return (string) $result->getInsertedId();
    }

    /**
     * Recherche les documents d'une collection dont le champ $field correspond à $value.
     * Les résultats sont triés par created_at décroissant.
     */
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

    /**
     * Retrouve un document unique par son _id MongoDB.
     * Retourne null si l'identifiant est invalide ou si le document est introuvable.
     */
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

    /**
     * Recherche les documents d'une collection dont le champ "status" correspond à $status.
     * Les résultats sont triés par created_at décroissant.
     */
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

    /**
     * Met à jour les champs spécifiés dans $update d'un document identifié par son _id.
     * Retourne true si au moins un document a été trouvé et modifié, false sinon.
     */
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
     * Recherche des documents selon des filtres optionnels (menu_id, date_from, date_to).
     * Les champs de date sont comparés sous forme de chaînes au format « Y-m-d H:i:s ».
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
     * Compte le nombre de documents d'une collection dont le champ $field vaut $value.
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
