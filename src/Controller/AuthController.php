<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\PasswordResetToken;
use App\Repository\PasswordResetTokenRepository;
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

        if (mb_strlen($password) < 8 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/', $password)) {
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

    /**
     * POST /auth/forgot-password
     *
     * Initie la procédure de réinitialisation de mot de passe.
     * Si l'e-mail correspond à un compte existant, un token est généré et envoyé par e-mail.
     * La réponse est toujours identique (200 + même message) pour éviter l'énumération des comptes.
     */
    #[Route('/auth/forgot-password', name: 'auth_forgot_password', methods: ['POST'])]
    public function forgotPassword(
        Request $request,
        UserRepository $userRepository,
        PasswordResetTokenRepository $tokenRepository,
        EntityManagerInterface $entityManager,
        MailService $mailService,
        LoggerInterface $logger
    ): JsonResponse {
        // Message neutre renvoyé dans tous les cas pour éviter d'indiquer si un compte existe
        $neutralMessage = 'Si un compte est associé à cet e-mail, un lien de réinitialisation vous a été envoyé.';

        $data  = json_decode($request->getContent(), true);
        $email = trim((string) ($data['email'] ?? ''));

        // Validation basique du format d'e-mail
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // On retourne le même message neutre même pour un e-mail malformé
            return new JsonResponse(['message' => $neutralMessage]);
        }

        $user = $userRepository->findOneBy(['email' => $email]);

        // Si aucun compte n'est trouvé, on répond identiquement pour éviter l'énumération
        if (!$user) {
            return new JsonResponse(['message' => $neutralMessage]);
        }

        // Génération d'un token cryptographiquement sûr (64 caractères hex = 32 octets)
        $tokenValue = bin2hex(random_bytes(32));

        $now    = new \DateTimeImmutable();
        $prt    = new PasswordResetToken();
        $prt->setUser($user);
        $prt->setToken($tokenValue);
        $prt->setCreatedAt($now);
        // Expiration dans 1 heure
        $prt->setExpiresAt($now->modify('+1 hour'));

        try {
            $entityManager->persist($prt);
            $entityManager->flush();
        } catch (\Throwable $e) {
            $logger->error('Échec de la persistance du token de réinitialisation.', ['error' => $e->getMessage()]);
            // On retourne le message neutre pour ne pas exposer l'erreur interne
            return new JsonResponse(['message' => $neutralMessage]);
        }

        try {
            $mailService->sendPasswordReset($user, $tokenValue);
        } catch (\Throwable $e) {
            // L'échec de l'envoi e-mail ne doit pas révéler d'information à l'appelant
            $logger->error('Échec de l\'envoi de l\'e-mail de réinitialisation.', ['error' => $e->getMessage()]);
        }

        return new JsonResponse(['message' => $neutralMessage]);
    }

    /**
     * POST /auth/reset-password
     *
     * Réinitialise le mot de passe de l'utilisateur à partir d'un token valide.
     * Vérifie que le token existe, n'est pas expiré et n'a pas déjà été utilisé.
     * Hache le nouveau mot de passe et marque le token comme utilisé.
     */
    #[Route('/auth/reset-password', name: 'auth_reset_password', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        PasswordResetTokenRepository $tokenRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger
    ): JsonResponse {
        $data        = json_decode($request->getContent(), true);
        $tokenValue  = trim((string) ($data['token'] ?? ''));
        $newPassword = (string) ($data['password'] ?? '');

        if ($tokenValue === '' || $newPassword === '') {
            return new JsonResponse(['success' => false, 'message' => 'Données manquantes.'], 400);
        }

        // Validation de la complexité du mot de passe (identique à l'inscription)
        if (
            mb_strlen($newPassword) < 8
            || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/', $newPassword)
        ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial.',
            ], 400);
        }

        // Recherche du token (valide = non expiré + non utilisé)
        $prt = $tokenRepository->findValidToken($tokenValue);

        if (!$prt) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Ce lien de réinitialisation est invalide ou a expiré.',
            ], 400);
        }

        $user = $prt->getUser();

        // Hachage du nouveau mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        // Marque le token comme utilisé pour empêcher sa réutilisation
        $prt->setUsedAt(new \DateTimeImmutable());

        try {
            $entityManager->flush();
        } catch (\Throwable $e) {
            $logger->error('Échec de la réinitialisation du mot de passe.', ['error' => $e->getMessage()]);
            return new JsonResponse(['success' => false, 'message' => 'Erreur interne.'], 500);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Votre mot de passe a été réinitialisé avec succès.',
        ]);
    }
}
