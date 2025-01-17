<?php

namespace App\Service;

use App\Entity\Experience;
use Doctrine\ORM\EntityManagerInterface;

class ExperienceService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAllOrderedByFromDateQuery()
    {
        return $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Experience::class, 'e')
            ->orderBy('e.fromdate', 'DESC')
            ->getQuery();
    }

    public function findById(int $id): ?Experience
    {
        return $this->entityManager->getRepository(Experience::class)->find($id);
    }

    public function saveExperience(Experience $experience): void
    {
        $this->entityManager->persist($experience);
        $this->entityManager->flush();
    }

    public function deleteExperience(Experience $experience): void
    {
        $this->entityManager->remove($experience);
        $this->entityManager->flush();
    }

    
}
