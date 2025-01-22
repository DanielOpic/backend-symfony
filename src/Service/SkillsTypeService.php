<?php
namespace App\Service;

use App\Repository\SkillsTypeRepository;

class SkillsTypeService
{
    private $skillsTypeRepository;

    public function __construct(SkillsTypeRepository $skillsTypeRepository)
    {
        $this->skillsTypeRepository = $skillsTypeRepository;
    }

    public function getGroupedSkillsByType()
    {
        return $this->skillsTypeRepository->findSkillsGroupedByType();
    }
}
