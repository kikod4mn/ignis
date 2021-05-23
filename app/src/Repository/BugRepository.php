<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Bug;
use App\Repository\Concerns\RepositoryUuidFinderConcern;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Bug|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bug|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bug[]    findAll()
 * @method Bug[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BugRepository extends ServiceEntityRepository {
	use RepositoryUuidFinderConcern;
	
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Bug::class);
	}
	
	public function getOneUnFixedBug(): ?Bug {
		return $this->findOneBy(['fixed' => false]);
	}
}
