<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 1000)]
    private ?string $password = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    // Metody wymagane przez UserInterface

    public function getUserIdentifier(): string
    {
        return $this->name;  // Zwracamy 'name' jako identyfikator użytkownika
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];  // Domyślna rola użytkownika
    }

    public function getSalt(): ?string
    {
        return null;  // W przypadku bcrypt lub innych algorytmów nie jest wymagany salt
    }

    public function eraseCredentials(): void
    {
        // Można tutaj usunąć ewentualne dane, które mogą być wrażliwe (np. hasło w formie jawnej)
    }
}
