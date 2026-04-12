<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use App\Service\MongoDBService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class ReviewController extends AbstractController
{
    public function __construct(
        private MongoDBService  $mongo,
        private OrderRepository $orderRepository,
        private UserRepository  $userRepository,
    ) {}

    /**
     * POST /api/reviews
     * Create a review for a "terminée" order belonging to the current user.
     */
    #[Route('/reviews', name: 'api_review_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }

        $data    = json_decode($request->getContent(), true) ?? [];
        $orderId = (int) ($data['order_id'] ?? 0);
        $rating  = (int) ($data['rating']   ?? 0);
        $comment = strip_tags(trim((string) ($data['comment'] ?? '')));

        if ($orderId <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
            return new JsonResponse(['message' => 'Données invalides.'], 422);
        }

        if (mb_strlen($comment) > 1000) {
            return new JsonResponse(['message' => 'Le commentaire ne peut pas dépasser 1000 caractères.'], 422);
        }

        $order = $this->orderRepository->find($orderId);
        if (!$order || $order->getUser()?->getId() !== $user->getId()) {
            return new JsonResponse(['message' => 'Commande introuvable.'], 404);
        }

        if ($order->getStatus()->value !== 'terminée') {
            return new JsonResponse(['message' => 'Vous ne pouvez commenter qu\'une commande terminée.'], 403);
        }

        // One review per order per user
        $existing = $this->mongo->findByField('reviews', 'order_id', $orderId);
        foreach ($existing as $rev) {
            if ((int) ($rev['user_id'] ?? 0) === $user->getId()) {
                return new JsonResponse(['message' => 'Vous avez déjà laissé un commentaire pour cette commande.'], 409);
            }
        }

        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $id  = $this->mongo->insertOne('reviews', [
            'order_id'   => $orderId,
            'user_id'    => $user->getId(),
            'rating'     => $rating,
            'comment'    => $comment,
            'created_at' => $now,
            'updated_at' => $now,
            'status'     => 'En attente de validation',
        ]);

        return new JsonResponse(['id' => $id, 'message' => 'Commentaire enregistré.'], 201);
    }

    /**
     * GET /api/reviews/my
     * Returns all reviews for the current user (including "En attente de validation").
     */
    #[Route('/reviews/my', name: 'api_review_my', methods: ['GET'])]
    public function my(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }

        $reviews = $this->mongo->findByField('reviews', 'user_id', $user->getId());

        return new JsonResponse($reviews);
    }

    /**
     * PUT /api/reviews/{id}
     * User updates a "Non validé" review (re-submits for moderation).
     */
    #[Route('/reviews/{id}', name: 'api_review_update', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }

        if (!preg_match('/^[0-9a-f]{24}$/i', $id)) {
            return new JsonResponse(['message' => 'Identifiant invalide.'], 400);
        }

        $review = $this->mongo->findOneById('reviews', $id);
        if (!$review) {
            return new JsonResponse(['message' => 'Commentaire introuvable.'], 404);
        }

        if ((int) ($review['user_id'] ?? 0) !== $user->getId()) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        if (($review['status'] ?? '') !== 'Non validé') {
            return new JsonResponse(['message' => 'Ce commentaire ne peut pas être modifié.'], 403);
        }

        $data    = json_decode($request->getContent(), true) ?? [];
        $rating  = (int) ($data['rating']  ?? 0);
        $comment = strip_tags(trim((string) ($data['comment'] ?? '')));

        if ($rating < 1 || $rating > 5 || $comment === '') {
            return new JsonResponse(['message' => 'Données invalides.'], 422);
        }

        if (mb_strlen($comment) > 1000) {
            return new JsonResponse(['message' => 'Le commentaire ne peut pas dépasser 1000 caractères.'], 422);
        }

        $this->mongo->updateOneById('reviews', $id, [
            'rating'     => $rating,
            'comment'    => $comment,
            'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            'status'     => 'En attente de validation',
        ]);

        return new JsonResponse(['message' => 'Commentaire mis à jour, en attente de validation.']);
    }

    /**
     * GET /api/staff/reviews
     * Returns all "En attente de validation" reviews enriched with user first names.
     * Staff only.
     */
    #[Route('/staff/reviews', name: 'api_staff_reviews', methods: ['GET'])]
    public function staffIndex(): JsonResponse
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

        $reviews = $this->mongo->findByStatus('reviews', 'En attente de validation');

        foreach ($reviews as &$rev) {
            $userId   = (int) ($rev['user_id'] ?? 0);
            $reviewer = $userId > 0 ? $this->userRepository->find($userId) : null;
            $rev['user_name'] = $reviewer?->getName() ?? 'Client inconnu';
        }
        unset($rev);

        return new JsonResponse($reviews);
    }

    /**
     * PATCH /api/staff/reviews/{id}/status
     * Staff validates or refuses a review.
     */
    #[Route('/staff/reviews/{id}/status', name: 'api_staff_review_status', methods: ['PATCH'])]
    public function staffUpdateStatus(string $id, Request $request): JsonResponse
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

        if (!preg_match('/^[0-9a-f]{24}$/i', $id)) {
            return new JsonResponse(['message' => 'Identifiant invalide.'], 400);
        }

        $data      = json_decode($request->getContent(), true) ?? [];
        $action    = (string) ($data['action'] ?? '');
        $newStatus = match ($action) {
            'validate' => 'Validé',
            'refuse'   => 'Non validé',
            default    => null,
        };

        if ($newStatus === null) {
            return new JsonResponse(['message' => 'Action invalide. Utilisez "validate" ou "refuse".'], 422);
        }

        $review = $this->mongo->findOneById('reviews', $id);
        if (!$review) {
            return new JsonResponse(['message' => 'Commentaire introuvable.'], 404);
        }

        $this->mongo->updateOneById('reviews', $id, [
            'status'     => $newStatus,
            'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);

        return new JsonResponse(['message' => 'Statut mis à jour.', 'status' => $newStatus]);
    }
}
