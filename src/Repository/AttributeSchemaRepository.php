<?php

namespace App\Repository;

use App\Entity\AttributeSchema;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AttributeSchemaRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttributeSchema::class);
    }

    public function getPriority(string $code)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.code = :val')
            ->setParameter('val', $code)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }
    
}