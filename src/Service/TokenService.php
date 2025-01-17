<?php

namespace App\Service;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenService
{
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    /**
     * Weryfikacja i dekodowanie tokena JWT.
     */
    public function parseToken(string $token): ?array
    {
        try {
            return $this->jwtManager->parse($token);
        } catch (\Exception $e) {
            return null; // Jeśli token jest niepoprawny, zwracamy null
        }
    }

    /**
     * Generowanie nowego tokena JWT dla użytkownika.
     */
    public function createToken(UserInterface $user): string
    {
        return $this->jwtManager->create($user);
    }
}
