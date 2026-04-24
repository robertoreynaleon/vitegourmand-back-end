<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Contrôleur d'authentification.
 * Gère la connexion et la création de compte utilisateur.
 */
class AuthController extends AbstractController
{
    /**
     * POST /auth/login
     *
     * Authentifie un utilisateur par email et mot de passe.
     * Retourne un token JWT ainsi que les données de l'utilisateur si les identifiants sont valides.
     */
    #[Route('/auth/login', name: 'auth_login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        // Vérification que les deux champs sont renseignés
        if ($email === '' || $password === '') {
            return new JsonResponse(['code' => 400, 'message' => 'Email et mot de passe requis.'], 400);
        }

        // Recherche de l'utilisateur par email
        $user = $userRepository->findOneBy(['email' => $email]);

        // Si l'utilisateur n'existe pas ou que le mot de passe est incorrect, on renvoie une 401
        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['code' => 401, 'message' => 'Identifiants invalides.'], 401);
        }

        // Génération du token JWT signé avec la clé privée RS256
        $token = $jwtManager->create($user);

        return new JsonResponse([
            'token' => $token,
            'user'  => [
                'id'         => $user->getId(),
                'email'      => $user->getEmail(),
                'name'       => $user->getName(),
                'lastname'   => $user->getLastname(),
                'phone'      => $user->getPhone(),
                'address'    => $user->getAddress(),
                'city'       => $user->getCity(),
                'postalCode' => $user->getPostalCode(),
                'roles'      => $user->getRoles(),
            ],
        ]);
    }

    /**
     * POST /auth/register/
     *
     * Crée un nouveau compte utilisateur avec le rôle « client ».
     * Valide tous les champs, hache le mot de passe, persiste l'utilisateur
     * et envoie un e-mail de bienvenue.
     */
    #[Route('/auth/register/', name: 'auth_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger,
        CsrfTokenManagerInterface $csrfTokenManager,
        MailService $mailService
    ): JsonResponse {
        // Lecture des données POST (formulaire x-www-form-urlencoded)
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

        // Vérification CSRF optionnelle — le frontend peut envoyer _csrf_token pour plus de sécurité
        $csrfTokenValue = (string) ($post['_csrf_token'] ?? '');
        if ($csrfTokenValue !== '') {
            $token = new CsrfToken('register', $csrfTokenValue);
            if (!$csrfTokenManager->isTokenValid($token)) {
                $logger->warning('Tentative d\'inscription avec un token CSRF invalide.');
                return new JsonResponse(['success' => false, 'message' => 'Requête invalide.'], 400);
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

        try {
            $mailService->sendWelcome($user);
        } catch (\Throwable $e) {
            // L'échec de l'e-mail n'annule pas l'inscription — on logue juste l'erreur
            $logger->error('Échec d\'envoi de l\'e-mail de bienvenue.', ['error' => $e->getMessage()]);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Inscription reussie.'
        ], 201);
    }
}
