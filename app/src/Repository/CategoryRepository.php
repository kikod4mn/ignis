<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Category;
use App\Repository\Concerns\RepositoryUuidFinderConcern;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository {
	use RepositoryUuidFinderConcern;
	
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Category::class);
	}
//
//	public function findOneByRedis(array $criteria, array $orderBy = null): ?Category {
//		$qb = $this->getQueryBuilder('c');
//		foreach ($criteria as $key => $value) {
//			$qb->andWhere(sprintf('c.%s = :%s', $key, $key))
//			   ->setParameter(sprintf('c.%s', $key), $value)
//			;
//		}
//		if ($orderBy !== null) {
//			foreach ($orderBy as $key => $value) {
//				$qb->addOrderBy($key, $value);
//			}
//		}
//		return $qb->getQuery()->execute();
//	}
//
//	public function findAllByRedis(array $orderBy = null): array {
//		$qb = $this->getQueryBuilder('c');
//		if ($orderBy !== null) {
//			foreach ($orderBy as $key => $value) {
//				$qb->addOrderBy($key, $value);
//			}
//		}
//		return $qb->getQuery()->execute();
//	}
}
