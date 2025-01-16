<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

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

    // Sprawdzanie, czy użytkownik jest zalogowany na podstawie tokena JWT
    #[Route('/api/check-login', name: 'api_check_login', methods: ['GET'])]
    public function checkLogin(Request $request, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        // Pobieramy token z nagłówka Authorization
        $token = $request->headers->get('Authorization');

        // Jeśli token nie jest przesyłany w nagłówku, zwracamy błąd
        if (!$token) {
            return new JsonResponse(['message' => 'Token not provided'], 400);
        }

        // Usuwamy prefix "Bearer " (jeśli jest) z tokena
        $token = str_replace('Bearer ', '', $token);

        try {
            // Próba weryfikacji tokena
            $decodedToken = $jwtManager->parse($token);

            // Jeśli token jest poprawny, zwróć informacje, że użytkownik jest zalogowany
            if ($decodedToken) {
                return new JsonResponse(['isAuthenticated' => true]);
            } else {
                return new JsonResponse(['isAuthenticated' => false], 401); // Unauthorized
            }
        } catch (\Exception $e) {
            return new JsonResponse(['isAuthenticated' => false], 401); // Unauthorized
        }
    }
    
}
