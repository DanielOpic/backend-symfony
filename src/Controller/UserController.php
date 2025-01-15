<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class UserController extends AbstractController
{
    #[Route('/add-user', name: 'add_user', methods: ['GET'])]
    public function addUser(UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): JsonResponse
    {
        die('ok');
        
        $user = new User();
        $user->setName('Admin');

        // Haszowanie hasła
        $hashedPassword = $userPasswordHasher->hashPassword($user, 'PASSWORD'); //Podmianka na bezpieczne hasło
        $user->setPassword($hashedPassword);

        // Zapisujemy użytkownika
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Użytkownik został dodany.']);
     
    }
}

