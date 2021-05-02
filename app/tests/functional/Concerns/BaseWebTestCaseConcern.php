<?php

declare(strict_types = 1);

namespace App\Tests\functional\Concerns;

use App\Entity\Bug;
use App\Entity\Category;
use App\Entity\Project;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityRepository;
use LogicException;
use function array_key_exists;
use function count;
use function mt_rand;
use function shuffle;
use function sprintf;

trait BaseWebTestCaseConcern {
	/**
	 * @var array<string, EntityRepository>
	 */
	private array $repoCache = [];
	
	public function getRepository(string $repoClass): EntityRepository {
		if (! array_key_exists($repoClass, $this->repoCache)) {
			$svc = static::$container->get($repoClass);
			if (! $svc instanceof $repoClass) {
				throw new LogicException(sprintf('Cannot find service "%s"', $repoClass));
			}
			$this->repoCache[$repoClass] = $svc;
		}
		return $this->repoCache[$repoClass];
	}
	
	protected function getOneAdmin(): User {
		/** @var UserRepository $userRepository */
		$userRepository = $this->getRepository(UserRepository::class);
		$projectLeads   = $userRepository->findByRoles([$this->getAdminRole()]);
		$count          = count($projectLeads) - 1;
		if ($count === 0) {
			throw new LogicException('No project leads were found!');
		}
		$user = $projectLeads[mt_rand(0, $count)];
		if (! $user instanceof User) {
			throw new LogicException(
				sprintf(
					'$user must be an instance of "%s". Got "%s" instead.',
					User::class, (string) $user
				)
			);
		}
		return $user;
	}
	
	protected function getOneProjectLead(): User {
		/** @var UserRepository $userRepository */
		$userRepository = $this->getRepository(UserRepository::class);
		$projectLeads   = $userRepository->findByRoles([$this->getProjectLeadRole()]);
		$count          = count($projectLeads) - 1;
		if ($count === 0) {
			throw new LogicException('No project leads were found!');
		}
		$user = $projectLeads[mt_rand(0, $count)];
		if (! $user instanceof User) {
			throw new LogicException(
				sprintf(
					'$user must be an instance of "%s". Got "%s" instead.',
					User::class, (string) $user
				)
			);
		}
		if ($user->getProjects()->isEmpty()) {
			return $this->getOneProjectLead();
		}
		return $user;
	}
	
	protected function getOneActiveUser(): User {
		/** @var UserRepository $userRepository */
		$userRepository = $this->getRepository(UserRepository::class);
		$users          = $userRepository->findByRoles([$this->getUserRole()]);
		$count          = count($users) - 1;
		if ($count === 0) {
			throw new LogicException('No project leads were found!');
		}
		$user = $users[mt_rand(0, $count)];
		if (! $user instanceof User) {
			throw new LogicException(
				sprintf(
					'$user must be an instance of "%s". Got "%s" instead.',
					User::class, (string) $user
				)
			);
		}
		if (! $user->getActive()) {
			return $this->getOneActiveUser();
		}
		return $user;
	}
	
	protected function getTestUser(): User {
		/** @var UserRepository $userRepository */
		$userRepository = $this->getRepository(UserRepository::class);
		$user           = $userRepository->findByRoles([$this->getTestUserRole()])[0];
		if (! $user instanceof User) {
			throw new LogicException(
				sprintf(
					'$user must be an instance of "%s". Got "%s" instead.',
					User::class, (string) $user
				)
			);
		}
		if (! $user->getActive()) {
			throw new LogicException('Test user is not active!');
		}
		return $user;
	}
	
	protected function getAdminRole(): Role {
		$role = $this->getRepository(RoleRepository::class)->findOneBy(['name' => Role::ROLE_ADMIN]);
		if (! $role instanceof Role) {
			throw new LogicException(
				sprintf(
					'$role must be an instance of "%s". No role found with name "%s".',
					Role::class, Role::ROLE_ADMIN
				)
			);
		}
		return $role;
	}
	
	protected function getProjectLeadRole(): Role {
		$role = $this->getRepository(RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		if (! $role instanceof Role) {
			throw new LogicException(
				sprintf(
					'$role must be an instance of "%s". No role found with name "%s".',
					Role::class, Role::ROLE_PROJECT_LEAD
				)
			);
		}
		return $role;
	}
	
	protected function getUserRole(): Role {
		$role = $this->getRepository(RoleRepository::class)->findOneBy(['name' => Role::ROLE_USER]);
		if (! $role instanceof Role) {
			throw new LogicException(
				sprintf(
					'$role must be an instance of "%s". No role found with name "%s".',
					Role::class, Role::ROLE_USER
				)
			);
		}
		return $role;
	}
	
	protected function getTestUserRole(): Role {
		$role = $this->getRepository(RoleRepository::class)->findOneBy(['name' => Role::ROLE_TEST_USER]);
		if (! $role instanceof Role) {
			throw new LogicException(
				sprintf(
					'$role must be an instance of "%s". No role found with name "%s".',
					Role::class, Role::ROLE_TEST_USER
				)
			);
		}
		return $role;
	}
	
	protected function getOneProject(): Project {
		$project = $this->getOneProjectLead()->getProjects()->first();
		if (! $project instanceof Project) {
			throw new LogicException('No projects for this particular user. Try to find a better project lead!');
		}
		return $project;
	}
	
	protected function getOneBug(): Bug {
		$bugs = $this->getOneProject()->getBugs();
		if ($bugs->count() < 1) {
			return $this->getOneBug();
		}
		return $bugs->first();
	}
	
	protected function getOneProjectWithFeatures(): Project {
		$project = $this->getOneProject();
		if ($project->getFeatures()->isEmpty()) {
			return $this->getOneProjectWithFeatures();
		}
		return $project;
	}
	
	protected function getOneCategoryNot(Project $project): Category {
		$categoryRepository = $this->getRepository(CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		return $this->categoryNot($categories, $project->getCategory());
	}
	
	/**
	 * @param   array<int, Category>   $categories
	 * @return Category
	 */
	private function categoryNot(array $categories, Category $category): Category {
		shuffle($categories);
		if ($categories[0]->getId() !== $category->getId()) {
			return $categories[0];
		}
		return $this->categoryNot($categories, $category);
	}
}