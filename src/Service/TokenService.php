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

    public function validateToken(string $authorizationHeader): bool
    {
        // Wyciągamy token z nagłówka
        $token = $this->extractToken($authorizationHeader);

        // Sprawdzamy, czy token jest prawidłowy
        if (!$token) {
            return false;
        }

        // Dekodujemy token, aby uzyskać jego zawartość
        $decoded = $this->parseToken($token);

        // Jeśli token jest niepoprawny (np. nie istnieje, jest nieważny), zwróć fałsz
        if (!$decoded) {
            return false;
        }

        // Przykład dodatkowej weryfikacji:
        // Sprawdzamy datę ważności tokena
        $expiryDate = $decoded['exp'] ?? null;
        if ($expiryDate && $expiryDate < time()) {
            return false; // Token wygasł
        }

        return true;
    }

    /**
     * Weryfikacja i dekodowanie tokena JWT.
     */
    public function parseToken(string $token): ?array
    {
        try {
            // Próbujemy zwrócić zawartość zdekodowanego tokena
            return $this->jwtManager->parse($token);
        } catch (\Exception $e) {
            // W przypadku błędu zwracamy null (token jest niepoprawny)
            return null;
        }
    }

    /**
     * Generowanie nowego tokena JWT dla użytkownika.
     */
    public function createToken(UserInterface $user): string
    {
        return $this->jwtManager->create($user);
    }

    /**
     * Wyciągnięcie tokena.
     */
    public function extractToken(string $authorizationHeader): ?string
    {
        if (str_starts_with($authorizationHeader, 'Bearer ')) {
            return substr($authorizationHeader, 7);
        }
        return null;
    }
}
