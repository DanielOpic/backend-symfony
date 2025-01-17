<?php
namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AuthenticationService
{
    private EntityManagerInterface $entityManager;
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(EntityManagerInterface $entityManager, JWTTokenManagerInterface $jwtManager)
    {
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;
    }

    public function authenticate(string $username, string $password): ?string
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['name' => $username]);

        if ($user && $user->getPassword() === $password) {
            return $this->jwtManager->create($user);
        }

        return null;
    }
}
