<?php
namespace App\Controller\Api;

use App\Entity\User;
use App\Service\TokenService;
use App\Service\JsonResponseService;
use App\Service\AuthenticationService;
use App\Service\ValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Psr\Log\LoggerInterface; // Dodanie interfejsu loggera

final class LoginController extends AbstractController
{
    private TokenService $tokenService;
    private JsonResponseService $jsonResponseService;
    private LoggerInterface $logger; // Definicja loggera

    // Konstruktor do wstrzykiwania serwisów
    public function __construct(TokenService $tokenService, JsonResponseService $jsonResponseService, LoggerInterface $logger)
    {
        $this->tokenService = $tokenService;
        $this->jsonResponseService = $jsonResponseService;
        $this->logger = $logger; // Inicjalizacja loggera
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, AuthenticationService $authenticationService, ValidationService $validationService): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $errors = $validationService->validateLoginData($data);
            if (!empty($errors)) {
                return $this->jsonResponseService->createJsonResponse(['errors' => $errors], 400);
            }

            $username = $data['username'];
            $password = $data['password'];

            $token = $authenticationService->authenticate($username, $password);

            if ($token) {
                return $this->jsonResponseService->createJsonResponse(['message' => 'Login successful', 'token' => $token]);
            }

            return $this->jsonResponseService->createJsonResponse(['message' => 'Invalid credentials'], 401);
        } catch (\Exception $e) {
            // Logowanie błędów
            $this->logger->error('Login error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->getContent()
            ]);
            
            return $this->jsonResponseService->createJsonResponse(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    // Wylogowanie użytkownika
    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        try {
            // Możemy np. zwrócić, że "wylogowanie" się powiodło
            return $this->jsonResponseService->createJsonResponse(['message' => 'Logout successful']);
        } catch (\Exception $e) {
            // Logowanie błędów
            $this->logger->error('Logout error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return $this->jsonResponseService->createJsonResponse(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    // Sprawdzanie, czy użytkownik jest zalogowany na podstawie tokena JWT
    #[Route('/api/check-login', name: 'api_check_login', methods: ['GET'])]
    public function checkLogin(Request $request): JsonResponse
    {
        try {
            $authorizationHeader = $request->headers->get('Authorization');

            if (!$authorizationHeader) {
                return $this->jsonResponseService->createJsonResponse(['message' => 'Token not provided'], 400);
            }

            $token = $this->tokenService->extractToken($authorizationHeader);

            if (!$token || !$this->tokenService->parseToken($token)) {
                return $this->jsonResponseService->createJsonResponse(['isAuthenticated' => false], 401);
            }

            return $this->jsonResponseService->createJsonResponse(['isAuthenticated' => true]);
        } catch (\Exception $e) {
            // Logowanie błędów
            $this->logger->error('Check-login error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->getContent()
            ]);
            return $this->jsonResponseService->createJsonResponse(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
