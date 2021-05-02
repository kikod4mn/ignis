<?php

namespace App\Repository;

use App\Entity\HistoryEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HistoryEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoryEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoryEntity[]    findAll()
 * @method HistoryEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoryEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoryEntity::class);
    }

    // /**
    //  * @return HistoryEntity[] Returns an array of HistoryEntity objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HistoryEntity
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
