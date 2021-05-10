<?php

declare(strict_types = 1);

namespace App\Tests\Integration;

use App\Entity\Bug;
use App\Entity\Category;
use App\Entity\Feature;
use App\Entity\Language;
use App\Entity\Project;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class IntegrationHelp {
	/** @var array<string, EntityRepository<Entity>> */
	private static array $repoCache = [];
	
	/** @var array<int, User> */
	private static array $activeUsers = [];
	
	/** @var array<int, User> */
	private static array $inActiveUsers = [];
	
	/** @var array<int, User> */
	private static array $testUsers = [];
	
	/** @var array<int, User> */
	private static array $projectLeaders = [];
	
	/** @var array<int, User> */
	private static array $administrators = [];
	
	/** @var array<int, Project> */
	private static array $projects = [];
	
	/** @var array<int, Bug> */
	private static array $bugs = [];
	
	/** @var array<int, Category> */
	private static array $categories = [];
	
	/** @var array<int, Feature> */
	private static array $features = [];
	
	/** @var array<int, Language> */
	private static array $languages = [];
	
	public static function getActiveUser(ContainerInterface $container): User {
		if (count(self::$activeUsers) === 0) {
			/** @var UserRepository $userRepository */
			$userRepository    = self::getRepository($container, UserRepository::class);
			self::$activeUsers = array_filter(
				$userRepository->findByRoles([self::getRole($container)]),
				static fn (User $user): bool => $user->getActive()
			);
		}
		return self::$activeUsers[array_rand(self::$activeUsers)];
	}
	
	public static function getInActiveUserWithEmailConfirmed(ContainerInterface $container): User {
		if (count(self::$inActiveUsers) === 0) {
			/** @var UserRepository $userRepository */
			$userRepository      = self::getRepository($container, UserRepository::class);
			self::$inActiveUsers = array_filter(
				$userRepository->findByRoles([self::getRole($container)]),
				static fn (User $user): bool => ! $user->getActive()
												&& $user->getEmailConfirmToken() === null
												&& $user->getEmailConfirmedAt() !== null
			);
		}
		return self::$inActiveUsers[array_rand(self::$inActiveUsers)];
	}
	
	public static function getTestUser(ContainerInterface $container): User {
		if (count(self::$testUsers) === 0) {
			/** @var UserRepository $userRepository */
			$userRepository  = self::getRepository($container, UserRepository::class);
			self::$testUsers = array_filter(
				$userRepository->findByRoles([self::getRole($container, Role::ROLE_TEST_USER)]),
				static fn (User $user): bool => $user->getActive()
			);
		}
		return self::$testUsers[array_rand(self::$testUsers)];
	}
	
	public static function getProjectLeader(ContainerInterface $container): User {
		if (count(self::$projectLeaders) === 0) {
			/** @var UserRepository $userRepository */
			$userRepository       = self::getRepository($container, UserRepository::class);
			self::$projectLeaders = array_filter(
				$userRepository->findByRoles([self::getRole($container, Role::ROLE_PROJECT_LEAD)]),
				static fn (User $user): bool => $user->getActive()
			);
		}
		return self::$projectLeaders[array_rand(self::$projectLeaders)];
	}
	
	public static function getAdministrator(ContainerInterface $container): User {
		if (count(self::$administrators) === 0) {
			/** @var UserRepository $userRepository */
			$userRepository       = self::getRepository($container, UserRepository::class);
			self::$administrators = array_filter(
				$userRepository->findByRoles([self::getRole($container, Role::ROLE_ADMIN)]),
				static fn (User $user): bool => $user->getActive()
			);
		}
		return self::$administrators[array_rand(self::$administrators)];
	}
	
	private static function getRole(ContainerInterface $container, string $roleName = Role::ROLE_USER): Role {
		/** @var ?Role $role */
		$role = self::getRepository($container, RoleRepository::class)->findOneBy(['name' => $roleName]);
		if (! $role instanceof Role) {
			throw new RuntimeException(sprintf('$role must be an instance of "%s". No role found with name "%s".', Role::class, Role::ROLE_USER));
		}
		return $role;
	}
	
	/**
	 * @param   ContainerInterface   $container
	 * @param   string               $repoFQN
	 * @return EntityRepository<Entity>
	 */
	public static function getRepository(ContainerInterface $container, string $repoFQN): EntityRepository {
		// trouble with what? why does the repository when cached, retrieve stale copies of entities
		$svc = $container->get($repoFQN);
		if (! $svc instanceof EntityRepository) {
			throw new RuntimeException(sprintf('Cannot find service "%s"', $repoFQN));
		}
		return $svc;
//		if (! array_key_exists($repoFQN, self::$repoCache)) {
//			$svc = $container->get($repoFQN);
//			if (! $svc instanceof EntityRepository) {
//				throw new RuntimeException(sprintf('Cannot find service "%s"', $repoFQN));
//			}
//			self::$repoCache[$repoFQN] = $svc;
//		}
//		return self::$repoCache[$repoFQN];
	}
	
	public static function getCsrf(ContainerInterface $container): CsrfTokenManagerInterface {
		$tokenMgr = $container->get('security.csrf.token_manager');
		if (! $tokenMgr instanceof CsrfTokenManagerInterface) {
			if (is_null($tokenMgr)) {
				throw new RuntimeException('Var $tokenMgr is null.');
			}
			throw new RuntimeException(sprintf('Var $tokenMgr is an invalid type of class "%s"', $tokenMgr::class));
		}
		return $tokenMgr;
	}
}