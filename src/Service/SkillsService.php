<?php

namespace App\Service;

use App\Entity\Skills;
use Doctrine\ORM\EntityManagerInterface;

class SkillsService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findById(int $id): ?Skills
    {
        return $this->entityManager->getRepository(Skills::class)->find($id);
    }

    public function saveSkills(Skills $Skills): void
    {
        $this->entityManager->persist($Skills);
        $this->entityManager->flush();
    }

    public function deleteSkills(Skills $Skills): void
    {
        $this->entityManager->remove($Skills);
        $this->entityManager->flush();
    }

    
}
