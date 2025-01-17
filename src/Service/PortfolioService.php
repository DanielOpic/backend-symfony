<?php

namespace App\Service;

use App\Entity\Portfolio;
use Doctrine\ORM\EntityManagerInterface;

class PortfolioService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Pobiera wszystkie portfolia, posortowane według daty
    public function findAllOrderedByDateQuery()
    {
        return $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Portfolio::class, 'p')
            ->orderBy('p.fromdate', 'DESC')
            ->getQuery();
    }

    // Pobiera portfolio według ID
    public function findById(int $id): ?Portfolio
    {
        return $this->entityManager->getRepository(Portfolio::class)->find($id);
    }

    // Zapisuje nowe lub aktualizuje istniejące portfolio
    public function savePortfolio(Portfolio $portfolio): void
    {
        $this->entityManager->persist($portfolio);
        $this->entityManager->flush();
    }

    // Usuwa portfolio
    public function deletePortfolio(Portfolio $portfolio): void
    {
        $this->entityManager->remove($portfolio);
        $this->entityManager->flush();
    }
}
