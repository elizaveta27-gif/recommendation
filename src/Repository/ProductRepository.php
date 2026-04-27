<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }
    
    public function findById($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    
    public function findByAttributes(string $where, string $order, int $excludeId, int $limit = 10): array
    {
        $sql = sprintf(
            'SELECT * FROM product WHERE (%s) AND id != :id ORDER BY %s LIMIT :limit',
            $where,
            $order
        );

        return $this->getEntityManager()
            ->createNativeQuery($sql, $this->createResultSetMapping())
            ->setParameter('id', $excludeId)
            ->setParameter('limit', $limit)
            ->getResult();
    }

    private function createResultSetMapping(): \Doctrine\ORM\Query\ResultSetMappingBuilder
    {
        $rsm = new \Doctrine\ORM\Query\ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Product::class, 'p');
        return $rsm;
    }

}
