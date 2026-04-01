<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/user')]
class UserController extends AbstractController
{
    #[Route('/me', name: 'api_user_me', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
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
}
