<?php

namespace App\Controller;

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

#[Route('/api/user')]
class UserController extends AbstractController
{
    #[Route('/me', name: 'api_user_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
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

        if (mb_strlen($name) < 2) $errors[] = 'name';
        if (mb_strlen($lastname) < 2) $errors[] = 'lastname';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'email';
        if (!preg_match('/^0[1-9][0-9]{8}$/', $phone)) $errors[] = 'phone';
        if (mb_strlen($address) < 5) $errors[] = 'address';
        if (mb_strlen($city) < 2) $errors[] = 'city';
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

        // Vérifier si le nouvel email est déjà pris par un autre compte
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

        $userId = $currentUser->getId();

        try {
            // 1. Supprimer les reviews MongoDB (cascade via user_id)
            $mongo->deleteByField('reviews', 'user_id', $userId);

            // 2. Supprimer les commandes MySQL (OrderMenu supprimés en cascade via l'entité Order)
            $orders = $orderRepository->findBy(['user' => $currentUser]);
            foreach ($orders as $order) {
                $entityManager->remove($order);
            }

            // 3. Supprimer l'utilisateur
            $entityManager->remove($currentUser);
            $entityManager->flush();

        } catch (\Throwable $e) {
            $logger->error('Account deletion failed.', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de la suppression du compte.'], 500);
        }

        return new JsonResponse(['success' => true], 200);
    }
}
