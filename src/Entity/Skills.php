<?php

namespace App\Entity;

use App\Repository\SkillsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SkillsRepository::class)]
class Skills
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: SkillsType::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private SkillsType $skillsType;

    #[ORM\Column(type: 'string', length: 250)]
    private string $name;

    #[ORM\Column(type: 'string', length: 100)]
    private string $fa;

    public function getId(): ?int
    {
        return $id;
    }

    public function getSkillsType(): SkillsType
    {
        return $this->skillsType;
    }

    public function setSkillsType(SkillsType $skillsType): self
    {
        $this->skillsType = $skillsType;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getFa(): string
    {
        return $this->fa;
    }

    public function setFa(string $fa): self
    {
        $this->fa = $fa;
        return $this;
    }
}
