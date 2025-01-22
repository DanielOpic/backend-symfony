<?php
namespace App\DTO;

class SkillsDTO
{
    private string $name;
    private string $fa;
    private SkillsTypeDTO $skillsType;

    public function __construct(string $name, string|null $fa, SkillsTypeDTO $skillsType)
    {
        $this->name = $name;
        $this->fa = ($fa)?$fa:'';
        $this->skillsType = $skillsType;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFa(): string
    {
        return $this->fa;
    }

    public function getSkillsType(): SkillsTypeDTO
    {
        return $this->skillsType;
    }
}
