<?php

namespace App\Repository;

use App\Entity\SkillsType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<SkillsType>
 */
class SkillsTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SkillsType::class);
    }

    public function findSkillsGroupedByType($page = 1, $limit = 100)
    {
       
        $qb = $this->createQueryBuilder('st')
            ->leftJoin('st.skills', 's')  // JOIN Skills
            ->addSelect('s')
            ->orderBy('st.name', 'ASC')
            ->addOrderBy('s.name', 'ASC');
        
        // Implementacja paginacji
        $qb->setFirstResult(($page - 1) * $limit) // Wartość offset
           ->setMaxResults($limit); // Maksymalna liczba wyników

        $result = $qb->getQuery()->getResult();

        $groupedData = [];
        
        // Grupowanie danych według typu umiejętności
        foreach ($result as $skillType) {
            $skills = $skillType->getSkills();
            $typeName = $skillType->getName();

            // Jeżeli jeszcze nie mamy grupy dla tego typu, tworzę ją
            if (!isset($groupedData[$typeName])) {
                $groupedData[$typeName] = [
                    'name' => $typeName,
                    'color' => $skillType->getColor(),
                    'list' => [],
                ];
            }

            // Dodajemy umiejętności do grupy
            foreach ($skills as $skill) {
                $groupedData[$typeName]['list'][] = [
                    'id' => $skill->getId(),
                    'name' => $skill->getName(),
                    'fa' => $skill->getFa(),
                ];
            }
        }

        return array_values($groupedData);  // Zwracamy pogrupowane dane
      
        return [];
    }
}
