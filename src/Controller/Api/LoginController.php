<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Response;

final class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, EntityManagerInterface $entityManager, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['username']) && isset($data['password'])) {
            $username = $data['username'];
            $password = $data['password'];  // Hasło przesłane przez klienta (po stronie React)

            // Wyszukujemy użytkownika w bazie danych na podstawie nazwy użytkownika
            $user = $entityManager->getRepository(User::class)->findOneBy(['name' => $username]);

            if ($user) {
                // Porównanie hasła: sprawdzamy, czy przesłane hasło (zahaszowane w React) jest zgodne z zapisanym w bazie
                if ($password === $user->getPassword()) {
                    // Generowanie tokena JWT
                    $token = $jwtManager->create($user);

                    // Jeśli hasła się zgadzają, zwracamy token
                    return new JsonResponse(['message' => 'Login successful', 'token' => $token]);
                } else {
                    return new JsonResponse(['message' => 'Invalid password'], 401); // Unauthorized
                }
            } else {
                return new JsonResponse(['message' => 'User not found'], 404); // Not Found
            }
        }

        return new JsonResponse(['message' => 'Invalid request'], 400); // Bad Request
    }

    // Wylogowanie użytkownika
    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // Możemy np. zwrócić, że "wylogowanie" się powiodło
        return new JsonResponse(['message' => 'Logout successful']);
    }
}
