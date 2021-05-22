<?php

namespace App\Repository;

use App\Entity\ConfirmEmailRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConfirmEmailRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfirmEmailRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfirmEmailRequest[]    findAll()
 * @method ConfirmEmailRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfirmEmailRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfirmEmailRequest::class);
    }

    // /**
    //  * @return ConfirmEmailRequest[] Returns an array of ConfirmEmailRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ConfirmEmailRequest
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
