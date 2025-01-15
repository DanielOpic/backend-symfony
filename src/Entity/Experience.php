<?php

namespace App\Entity;

use App\Repository\ExperienceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExperienceRepository::class)]
class Experience
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $company = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fromdate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $todate = null;

    #[ORM\Column(options: ["default" => 0])]
    private ?int $current = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getFromdate(): ?\DateTimeInterface
    {
        return $this->fromdate;
    }

    public function setFromdate(?\DateTimeInterface $fromdate): static
    {
        $this->fromdate = $fromdate;

        return $this;
    }

    public function getTodate(): ?\DateTimeInterface
    {
        return $this->todate;
    }

    public function setTodate(?\DateTimeInterface $todate): static
    {
        $this->todate = $todate;

        return $this;
    }

    public function getCurrent(): ?int
    {
        return $this->current;
    }

    public function setCurrent(int $current): static
    {
        $this->current = $current;

        return $this;
    }
}
