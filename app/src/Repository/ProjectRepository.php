<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\Concerns\RepositoryUuidFinderConcern;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository {
	use RepositoryUuidFinderConcern;
	
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Project::class);
	}
	
	public function findAllUserCanViewQ(User $user): Query {
		return $this->createQueryBuilder('p')
					->where(':viewer MEMBER OF p.canView')
					->setParameter('viewer', $user)
					->orWhere(':writer MEMBER OF p.canEdit')
					->setParameter('writer', $user)
					->orWhere(':author = p.author')
					->setParameter('author', $user)
					->getQuery()
			;
	}
}
