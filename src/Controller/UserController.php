<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use App\Service\MailService;
use App\Service\MongoDBService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur de l'espace utilisateur connecté.
 * Gère la consultation/modification du profil et la liste des commandes.
 * Toutes les routes sont protégées (/api/user/*) et nécessitent un JWT valide.
 */
#[Route('/api/user')]
class UserController extends AbstractController
{
    /**
     * GET /api/user/me
     *
     * Retourne le profil complet de l'utilisateur actuellement connecté.
     */
    #[Route('/me', name: 'api_user_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }

        // Seuls les clients peuvent accéder à leur espace personnel
        if (!in_array('ROLE_CLIENT', $user->getRoles(), true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        return new JsonResponse([
            'id'         => $user->getId(),
            'name'       => $user->getName(),
            'lastname'   => $user->getLastname(),
            'email'      => $user->getEmail(),
            'phone'      => $user->getPhone(),
            'address'    => $user->getAddress(),
            'city'       => $user->getCity(),
            'postalCode' => $user->getPostalCode(),
            'roles'      => $user->getRoles(),
        ]);
    }

    /**
     * PUT /api/user/me
     *
     * Met à jour le profil de l'utilisateur connecté (nom, email, adresse, etc.).
     * Si un nouveau mot de passe est fourni, il est validé puis haché.
     * Un e-mail de confirmation est envoyé dans les deux cas.
     */
    #[Route('/me', name: 'api_user_update', methods: ['PUT'])]
    public function update(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        MailService $mailService,
        LoggerInterface $logger
    ): JsonResponse {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }

        // Seuls les clients peuvent modifier leur profil
        if (!in_array('ROLE_CLIENT', $currentUser->getRoles(), true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $name        = trim((string) ($data['name'] ?? ''));
        $lastname    = trim((string) ($data['lastname'] ?? ''));
        $email       = trim((string) ($data['email'] ?? ''));
        $phone       = preg_replace('/\s+/', '', (string) ($data['phone'] ?? ''));
        $address     = trim((string) ($data['address'] ?? ''));
        $city        = trim((string) ($data['city'] ?? ''));
        $postalCode  = trim((string) ($data['postalCode'] ?? ''));
        $newPassword = (string) ($data['new_password'] ?? '');
        $passwordConfirm = (string) ($data['password_confirm'] ?? '');

        $errors = [];

        // Regex : lettres (dont accents), espaces, tirets, apostrophes
        $nameRegex = '/^[a-zA-Z\x{00C0}-\x{00FF}\s\-\']+$/u';
        // Regex : adresse postale (lettres, chiffres, espaces, ponctuation courante)
        $addressRegex = '/^[a-zA-Z\x{00C0}-\x{00FF}0-9\s\-\',.\/]+$/u';

        if (mb_strlen($name) < 2 || !preg_match($nameRegex, $name)) $errors[] = 'name';
        if (mb_strlen($lastname) < 2 || !preg_match($nameRegex, $lastname)) $errors[] = 'lastname';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'email';
        if (!preg_match('/^0[1-9][0-9]{8}$/', $phone)) $errors[] = 'phone';
        if (mb_strlen($address) < 5 || !preg_match($addressRegex, $address)) $errors[] = 'address';
        if (mb_strlen($city) < 2 || !preg_match($nameRegex, $city)) $errors[] = 'city';
        if (!preg_match('/^[0-9]{5}$/', $postalCode)) $errors[] = 'postalCode';

        $changePassword = $newPassword !== '';
        if ($changePassword) {
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{12,}$/', $newPassword)) {
                $errors[] = 'new_password';
            }
            if ($newPassword !== $passwordConfirm) {
                $errors[] = 'password_confirm';
            }
        }

        if (!empty($errors)) {
            return new JsonResponse(['success' => false, 'message' => 'Les informations fournies sont invalides.', 'fields' => $errors], 400);
        }

        // Vérification que le nouvel email n'est pas déjà utilisé par un autre compte
        if ($email !== $currentUser->getEmail()) {
            $existing = $userRepository->findOneBy(['email' => $email]);
            if ($existing && $existing->getId() !== $currentUser->getId()) {
                return new JsonResponse(['success' => false, 'message' => 'Cet email est déjà utilisé.'], 400);
            }
        }

        $currentUser->setName(strip_tags($name));
        $currentUser->setLastname(strip_tags($lastname));
        $currentUser->setEmail($email);
        $currentUser->setPhone($phone);
        $currentUser->setAddress(strip_tags($address));
        $currentUser->setCity(strip_tags($city));
        $currentUser->setPostalCode($postalCode);

        if ($changePassword) {
            $currentUser->setPassword($passwordHasher->hashPassword($currentUser, $newPassword));
        }

        try {
            $entityManager->flush();
        } catch (\Throwable $e) {
            $logger->error('Profile update failed.', ['error' => $e->getMessage()]);
            return new JsonResponse(['success' => false, 'message' => 'Erreur interne.'], 500);
        }

        try {
            $mailService->sendProfileUpdated($currentUser);
            if ($changePassword) {
                $mailService->sendPasswordChanged($currentUser);
            }
        } catch (\Throwable $e) {
            $logger->error('Profile update email failed.', ['error' => $e->getMessage()]);
        }

        return new JsonResponse([
            'success' => true,
            'user'    => [
                'id'         => $currentUser->getId(),
                'email'      => $currentUser->getEmail(),
                'name'       => $currentUser->getName(),
                'lastname'   => $currentUser->getLastname(),
                'phone'      => $currentUser->getPhone(),
                'address'    => $currentUser->getAddress(),
                'city'       => $currentUser->getCity(),
                'postalCode' => $currentUser->getPostalCode(),
                'roles'      => $currentUser->getRoles(),
            ],
        ]);
    }

    /**
     * DELETE /api/user/me
     *
     * Supprime définitivement le compte de l'utilisateur connecté.
     * Supprime d'abord ses avis MongoDB, puis ses commandes MySQL, puis l'entité utilisateur.
     */
    #[Route('/me', name: 'api_user_delete', methods: ['DELETE'])]
    public function delete(
        EntityManagerInterface $entityManager,
        OrderRepository $orderRepository,
        MongoDBService $mongo,
        LoggerInterface $logger
    ): JsonResponse {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }

        // Seuls les clients peuvent supprimer leur compte
        if (!in_array('ROLE_CLIENT', $currentUser->getRoles(), true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $userId = $currentUser->getId();

        try {
            // Étape 1 : suppression des avis MongoDB liés à cet utilisateur
            $mongo->deleteByField('reviews', 'user_id', $userId);

            // Étape 2 : suppression des commandes MySQL (les lignes OrderMenu sont en cascade)
            $orders = $orderRepository->findBy(['user' => $currentUser]);
            foreach ($orders as $order) {
                $entityManager->remove($order);
            }

            // Étape 3 : suppression de l'utilisateur lui-même
            $entityManager->remove($currentUser);
            $entityManager->flush();

        } catch (\Throwable $e) {
            $logger->error('Suppression de compte échouée.', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de la suppression du compte.'], 500);
        }

        return new JsonResponse(['success' => true], 200);
    }

    /**
     * GET /api/user/orders
     *
     * Retourne toutes les commandes de l'utilisateur connecté, triées par date décroissante.
     */
    #[Route('/orders', name: 'api_user_orders', methods: ['GET'])]
    public function orders(OrderRepository $orderRepository): JsonResponse
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }

        // Seuls les clients peuvent consulter leurs commandes
        if (!in_array('ROLE_CLIENT', $user->getRoles(), true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }

        $orders = $orderRepository->findBy(
            ['user' => $user],
            ['orderDate' => 'DESC']
        );

        $data = array_map(function (Order $order) {
            return [
                'id'              => $order->getId(),
                'orderDate'       => $order->getOrderDate()?->format('d/m/Y \à H:i'),
                'deliveryDate'    => $order->getDeliveryDate()?->format('d/m/Y'),
                'deliveryTime'    => $order->getDeliveryTime()?->format('H:i'),
                'deliveryAddress' => $order->getDeliveryAddress(),
                'status'          => $order->getStatus()->value,
                'totalAmount'     => $order->getTotalAmount(),
            ];
        }, $orders);

        return new JsonResponse($data);
    }
}
