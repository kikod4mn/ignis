<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\BackupEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BackupEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method BackupEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method BackupEntity[]    findAll()
 * @method BackupEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BackupEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BackupEntity::class);
    }

    // /**
    //  * @return BackupEntity[] Returns an array of BackupEntity objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BackupEntity
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
