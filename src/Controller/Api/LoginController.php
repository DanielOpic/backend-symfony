<?php

namespace App\Controller\Api; // Zmieniamy namespace na Api

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class LoginController extends AbstractController
{
    // Route z prefiksem /api i metodą POST
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Login endpoint',
            'path' => 'src/Controller/Api/LoginController.php',
        ]);
    }
}
