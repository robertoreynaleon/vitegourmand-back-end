<?php

namespace App\Controller;

use App\Service\MailService;
use App\Service\MongoDBService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    private const COLLECTION = 'contact_messages';

    private function checkStaffOrAdmin(): ?JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }
        $roles = $user->getRoles();
        if (!in_array('ROLE_ADMIN', $roles, true) && !in_array('ROLE_STAFF_MEMBER', $roles, true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }
        return null;
    }

    /**
     * POST /api/contact
     * Public endpoint — any visitor can submit a contact message.
     */
    #[Route('/api/contact', name: 'api_contact_submit', methods: ['POST'])]
    public function submit(Request $request, MongoDBService $mongoDBService): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $clientEmail = strtolower(trim((string) ($data['client_email'] ?? '')));
        $subject     = trim(strip_tags((string) ($data['subject'] ?? '')));
        $content     = trim(strip_tags((string) ($data['content'] ?? '')));

        $errors = [];
        if (!filter_var($clientEmail, FILTER_VALIDATE_EMAIL))    $errors[] = 'client_email';
        if (mb_strlen($subject) < 2 || mb_strlen($subject) > 150) $errors[] = 'subject';
        if (mb_strlen($content) < 10 || mb_strlen($content) > 3000) $errors[] = 'content';

        if (!empty($errors)) {
            return new JsonResponse(['errors' => $errors], 422);
        }

        $mongoDBService->insertOne(self::COLLECTION, [
            'client_email'  => $clientEmail,
            'subject'       => $subject,
            'content'       => $content,
            'sent_at'       => (new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')))->format('Y-m-d H:i:s'),
            'status'        => 'unread',
            'staff_response' => null,
            'replied_at'    => null,
        ]);

        return new JsonResponse(['message' => 'Message envoyé.'], 201);
    }

    /**
     * GET /api/staff/messages
     * Returns all contact messages sorted by sent_at DESC (staff + admin).
     */
    #[Route('/api/staff/messages', name: 'api_staff_messages_list', methods: ['GET'])]
    public function list(MongoDBService $mongoDBService): JsonResponse
    {
        if ($err = $this->checkStaffOrAdmin()) return $err;

        $docs = $mongoDBService->findAll(self::COLLECTION, 500);

        usort($docs, fn ($a, $b) => strcmp((string) ($b['sent_at'] ?? ''), (string) ($a['sent_at'] ?? '')));

        return new JsonResponse($docs);
    }

    /**
     * PATCH /api/staff/messages/{id}/read
     * Marks a message as read (staff + admin).
     */
    #[Route('/api/staff/messages/{id}/read', name: 'api_staff_messages_read', methods: ['PATCH'])]
    public function markRead(string $id, MongoDBService $mongoDBService): JsonResponse
    {
        if ($err = $this->checkStaffOrAdmin()) return $err;

        $doc = $mongoDBService->findOneById(self::COLLECTION, $id);
        if (!$doc) {
            return new JsonResponse(['message' => 'Message introuvable.'], 404);
        }

        if ($doc['status'] === 'unread') {
            $mongoDBService->updateOneById(self::COLLECTION, $id, ['status' => 'read']);
        }

        return new JsonResponse(['message' => 'Message marqué comme lu.']);
    }

    /**
     * PATCH /api/staff/messages/{id}/reply
     * Saves staff_response, sets status "replied", sends email (staff + admin).
     */
    #[Route('/api/staff/messages/{id}/reply', name: 'api_staff_messages_reply', methods: ['PATCH'])]
    public function reply(
        string $id,
        Request $request,
        MongoDBService $mongoDBService,
        MailService $mailService,
        LoggerInterface $logger
    ): JsonResponse {
        if ($err = $this->checkStaffOrAdmin()) return $err;

        $doc = $mongoDBService->findOneById(self::COLLECTION, $id);
        if (!$doc) {
            return new JsonResponse(['message' => 'Message introuvable.'], 404);
        }

        $data           = json_decode($request->getContent(), true) ?? [];
        $staffResponse  = trim(strip_tags((string) ($data['staff_response'] ?? '')));

        if (mb_strlen($staffResponse) < 5) {
            return new JsonResponse(['errors' => ['staff_response']], 422);
        }

        $mongoDBService->updateOneById(self::COLLECTION, $id, [
            'status'         => 'replied',
            'staff_response' => $staffResponse,
            'replied_at'     => (new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')))->format('Y-m-d H:i:s'),
        ]);

        try {
            $mailService->sendContactReply(
                $doc['client_email'],
                $doc['subject'],
                $staffResponse
            );
        } catch (\Throwable $e) {
            $logger->error('Contact reply email failed.', ['id' => $id, 'error' => $e->getMessage()]);
        }

        return new JsonResponse(['message' => 'Réponse envoyée.']);
    }
}
