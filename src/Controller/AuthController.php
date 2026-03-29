<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Psr\Log\LoggerInterface;

class AuthController extends AbstractController
{
    #[Route('/auth/register/', name: 'auth_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger,
        CsrfTokenManagerInterface $csrfTokenManager
    ): JsonResponse {
        // Use $_POST as requested, but fall back to Request for safety.
        $post = $_POST ?: $request->request->all();

        $name = trim((string) ($post['name'] ?? ''));
        $lastname = trim((string) ($post['lastname'] ?? ''));
        $email = trim((string) ($post['email'] ?? ''));
        $phone = trim((string) ($post['phone'] ?? ''));
        $address = trim((string) ($post['address'] ?? ''));
        $city = trim((string) ($post['city'] ?? ''));
        $postalCode = trim((string) ($post['postalCode'] ?? ''));
        $password = (string) ($post['password'] ?? '');
        $passwordConfirm = (string) ($post['password_confirm'] ?? '');

        // Optional CSRF check (front-end can send _csrf_token later).
        $csrfTokenValue = (string) ($post['_csrf_token'] ?? '');
        if ($csrfTokenValue !== '') {
            $token = new CsrfToken('register', $csrfTokenValue);
            if (!$csrfTokenManager->isTokenValid($token)) {
                $logger->warning('Invalid CSRF token during registration attempt.');
                return new JsonResponse(['success' => false, 'message' => 'Requete invalide.'], 400);
            }
        }

        $errors = [];

        if (mb_strlen($name) < 2) {
            $errors[] = 'name';
        }
        if (mb_strlen($lastname) < 2) {
            $errors[] = 'lastname';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'email';
        }

        $phoneSanitized = preg_replace('/\s+/', '', $phone);
        if ($phoneSanitized === '' || !preg_match('/^0[1-9][0-9]{8}$/', $phoneSanitized)) {
            $errors[] = 'phone';
        }

        if (mb_strlen($address) < 5) {
            $errors[] = 'address';
        }

        if (mb_strlen($city) < 2 || !preg_match('/^[a-zA-ZÀ-ÿ\s\-\']+$/', $city)) {
            $errors[] = 'city';
        }

        if (!preg_match('/^[0-9]{5}$/', $postalCode)) {
            $errors[] = 'postalCode';
        }

        if (mb_strlen($password) < 12 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{12,}$/', $password)) {
            $errors[] = 'password';
        }

        if ($password !== $passwordConfirm) {
            $errors[] = 'password_confirm';
        }

        if (!empty($errors)) {
            $logger->info('Registration validation failed.', ['fields' => $errors]);
            return new JsonResponse([
                'success' => false,
                'message' => 'Les informations fournies sont invalides.'
            ], 400);
        }

        $existingUser = $userRepository->findOneBy(['email' => $email]);
        if ($existingUser) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Les identifiants sont invalides.'
            ], 400);
        }

        $role = $roleRepository->findOneBy(['name' => 'client']) ?? $roleRepository->find(3);
        if (!$role) {
            $logger->error('Default role not found for registration.');
            return new JsonResponse(['success' => false, 'message' => 'Erreur interne.'], 500);
        }

        $user = new User();
        $user->setName(strip_tags($name));
        $user->setLastname(strip_tags($lastname));
        $user->setEmail($email);
        $user->setPhone($phoneSanitized);
        $user->setAddress(strip_tags($address));
        $user->setCity(strip_tags($city));
        $user->setPostalCode($postalCode);
        $user->setRole($role);

        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (\Throwable $e) {
            $logger->error('Registration failed.', ['error' => $e->getMessage()]);
            return new JsonResponse(['success' => false, 'message' => 'Erreur interne.'], 500);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Inscription reussie.'
        ], 201);
    }
}
