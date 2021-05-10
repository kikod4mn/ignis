<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\Concerns\RepositoryUuidFinderConcern;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use function array_key_last;
use function count;
use function dd;
use function explode;
use function mb_strtolower;
use function mb_substr;
use function sprintf;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository {
	use RepositoryUuidFinderConcern;
	
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, User::class);
	}
	
	/**
	 * @param   array<int, Role>   $roles
	 * @return array<int, User>
	 */
	public function findByRoles(array $roles): array {
		$qb = $this->createQueryBuilder('u');
		foreach ($roles as $role) {
			$qb->where(':role MEMBER OF u.roles')
			   ->setParameter('role', $role)
			;
		}
		return $qb->getQuery()->getResult();
	}
	
	/**
	 * @param   array<int, Role>   $roles
	 * @return null|User
	 */
	public function findOneByRoles(array $roles): ?User {
		$users = $this->findByRoles($roles);
		return $users[array_rand($users)];
	}
	
	/**
	 * Extract a Concern out of this maybe.
	 */
	public function randomOrFail(): User {
		$parts    = explode('\\', $this->getEntityName());
		$alias    = mb_strtolower(mb_substr($parts[array_key_last($parts)], 0, 1));
		$qb       = $this->createQueryBuilder($alias);
		$maxId    = (int) $qb->select(sprintf('Max(%s.id)', $alias))->getQuery()->getResult()[0][1];
		$randomId = mt_rand(1, $maxId);
		$qb->resetDQLParts();
		return $qb
			->select($alias)
			->from($this->getEntityName(), $alias)
			->where(sprintf('%s.id = :id', $alias))
			->setParameter('id', $randomId)
			->getQuery()
			->getSingleResult()
			;
	}
	
	/**
	 * @param   array<int, string>   $fields
	 * @return User
	 */
	public function oneWithNullFields(array $fields): User {
		$qb = $this->createQueryBuilder('u');
		foreach ($fields as $field) {
			$qb->andWhere($qb->expr()->isNull(sprintf('u.%s', $field)));
		}
		$results = $qb->getQuery()->getResult();
		if (count($results) === 0) {
			throw new LogicException(sprintf('Ran out of results for "%s". Refresh the fixtures!', $this->_class->name));
		}
		return $results[0];
	}
	
	/**
	 * @param   array<int, string>   $fields
	 * @return User
	 */
	public function oneWithNotNullFields(array $fields): User {
		$qb = $this->createQueryBuilder('u');
		foreach ($fields as $field) {
			$qb->andWhere($qb->expr()->isNotNull(sprintf('u.%s', $field)));
		}
		$results = $qb->getQuery()->getResult();
		if (count($results) === 0) {
			throw new LogicException(sprintf('Ran out of results for "%s". Refresh the fixtures!', $this->_class->name));
		}
		return $results[0];
	}
	
	public function findAllButNot(array $users): array {
		$qb = $this->createQueryBuilder('u');
		/** @var User $user */
		foreach ($users as $user) {
			$qb->orWhere('u.id != :id')
			   ->setParameter('id', $user->getId())
			;
		}
		return $qb->getQuery()->getResult();
	}
}
