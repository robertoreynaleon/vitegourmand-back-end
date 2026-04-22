<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin')]
class AdminController extends AbstractController
{
    private function checkAdmin(): ?JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], 401);
        }
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return new JsonResponse(['message' => 'Accès refusé.'], 403);
        }
        return null;
    }

    /**
     * GET /api/admin/staff
     * Returns all users with ROLE_STAFF_MEMBER (role name = "staff_member").
     */
    #[Route('/staff', name: 'api_admin_staff_list', methods: ['GET'])]
    public function staffList(UserRepository $userRepository, RoleRepository $roleRepository): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;

        $staffRole = $roleRepository->findOneBy(['name' => 'staff_member']);
        if (!$staffRole) {
            return new JsonResponse([]);
        }

        $users = $userRepository->findBy(['role' => $staffRole], ['lastname' => 'ASC']);

        $data = array_map(fn (User $u) => [
            'id'         => $u->getId(),
            'name'       => $u->getName(),
            'lastname'   => $u->getLastname(),
            'email'      => $u->getEmail(),
            'phone'      => $u->getPhone(),
            'address'    => $u->getAddress(),
            'city'       => $u->getCity(),
            'postalCode' => $u->getPostalCode(),
        ], $users);

        return new JsonResponse($data);
    }

    /**
     * POST /api/admin/staff
     * Creates a new STAFF_MEMBER user.
     */
    #[Route('/staff', name: 'api_admin_staff_create', methods: ['POST'])]
    public function staffCreate(
        Request $request,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        MailService $mailService,
        LoggerInterface $logger
    ): JsonResponse {
        if ($err = $this->checkAdmin()) return $err;

        $data = json_decode($request->getContent(), true) ?? [];

        $name       = trim((string) ($data['name']       ?? ''));
        $lastname   = trim((string) ($data['lastname']   ?? ''));
        $email      = strtolower(trim((string) ($data['email']    ?? '')));
        $phone      = preg_replace('/\s+/', '', (string) ($data['phone']    ?? ''));
        $address    = trim((string) ($data['address']    ?? ''));
        $city       = trim((string) ($data['city']       ?? ''));
        $postalCode = trim((string) ($data['postalCode'] ?? ''));
        $password   = (string) ($data['password'] ?? '');

        $errors = [];
        if (mb_strlen($name) < 2)                             $errors[] = 'name';
        if (mb_strlen($lastname) < 2)                         $errors[] = 'lastname';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))       $errors[] = 'email';
        if (!preg_match('/^0[1-9][0-9]{8}$/', $phone))       $errors[] = 'phone';
        if (mb_strlen($address) < 5)                          $errors[] = 'address';
        if (mb_strlen($city) < 2)                             $errors[] = 'city';
        if (!preg_match('/^[0-9]{5}$/', $postalCode))         $errors[] = 'postalCode';
        if (mb_strlen($password) < 12) $errors[] = 'password';

        if (!empty($errors)) {
            return new JsonResponse(['errors' => $errors], 422);
        }

        if ($userRepository->findOneBy(['email' => $email])) {
            return new JsonResponse(['errors' => ['email_taken']], 409);
        }

        $staffRole = $roleRepository->find(2);
        if (!$staffRole) {
            return new JsonResponse(['message' => 'Rôle staff introuvable.'], 500);
        }

        $newUser = new User();
        $newUser->setName($name);
        $newUser->setLastname($lastname);
        $newUser->setEmail($email);
        $newUser->setPhone($phone);
        $newUser->setAddress($address);
        $newUser->setCity($city);
        $newUser->setPostalCode($postalCode);
        $newUser->setRole($staffRole);
        $newUser->setPassword($passwordHasher->hashPassword($newUser, $password));

        $entityManager->persist($newUser);
        $entityManager->flush();

        try {
            $mailService->sendStaffWelcome($newUser);
        } catch (\Throwable $e) {
            $logger->error('Staff welcome email failed.', ['user_id' => $newUser->getId(), 'error' => $e->getMessage()]);
        }

        return new JsonResponse([
            'id'       => $newUser->getId(),
            'name'     => $newUser->getName(),
            'lastname' => $newUser->getLastname(),
            'email'    => $newUser->getEmail(),
            'phone'    => $newUser->getPhone(),
            'address'  => $newUser->getAddress(),
            'city'     => $newUser->getCity(),
            'postalCode' => $newUser->getPostalCode(),
        ], 201);
    }

    /**
     * DELETE /api/admin/staff/{id}
     * Deletes a STAFF_MEMBER user.
     */
    #[Route('/staff/{id}', name: 'api_admin_staff_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function staffDelete(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if ($err = $this->checkAdmin()) return $err;

        $user = $userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur introuvable.'], 404);
        }

        // Only staff members can be deleted through this endpoint
        if (!in_array('ROLE_STAFF_MEMBER', $user->getRoles(), true)) {
            return new JsonResponse(['message' => 'Seuls les employés peuvent être supprimés via cet endpoint.'], 403);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Employé supprimé.']);
    }
}
