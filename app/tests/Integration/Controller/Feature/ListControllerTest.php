<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Feature;

use App\Entity\Project;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Feature\ListController
 */
class ListControllerTest extends BaseWebTestCase {
	public function testListForUserWhoCanViewProject(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = array_filter(
			$projectRepository->findAll(),
			static fn (Project $p) => ! $p->getSoftDeleted()
									  && $p->getFeatures()->count() !== 0
									  && $p->getCanEdit()->count() !== 0
		);
		/** @var Project $project */
		$project = $projects[array_rand($projects)];
		$route   = sprintf('/projects/%s/features', $project->getUuid()?->toString());
		/** @var User $user */
		$user = $project->getCanView()->first();
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testListForProjectLead(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = array_filter(
			$projectRepository->findAll(),
			static fn (Project $p) => ! $p->getSoftDeleted()
									  && $p->getFeatures()->count() !== 0
									  && $p->getCanEdit()->count() !== 0
		);
		/** @var Project $project */
		$project = $projects[array_rand($projects)];
		$route   = sprintf('/projects/%s/features', $project->getUuid()?->toString());
		/** @var User $user */
		$user = $project->getAuthor();
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testListForTestUser(): void {
		/** @var RoleRepository $roleRepository */
		$roleRepository = IH::getRepository(static::$container, RoleRepository::class);
		/** @var Role $role */
		$role = $roleRepository->findOneBy(['name' => Role::ROLE_TEST_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = array_filter(
			$projectRepository->findAll(),
			static fn (Project $p) => ! $p->getSoftDeleted()
									  && $p->getFeatures()->count() !== 0
									  && $p->getCanEdit()->count() !== 0
		);
		/** @var Project $project */
		$project = $projects[array_rand($projects)];
		$route   = sprintf('/projects/%s/features', $project->getUuid()?->toString());
		/** @var User $user */
		$user = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testListDoesNotWorkForProjectLeadWhoCannotViewProject(): void {
		/** @var RoleRepository $roleRepository */
		$roleRepository = IH::getRepository(static::$container, RoleRepository::class);
		/** @var Role $role */
		$role = $roleRepository->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRoles([$role]);
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = array_filter(
			$projectRepository->findAll(),
			static fn (Project $p) => ! $p->getSoftDeleted()
									  && $p->getFeatures()->count() !== 0
									  && ! $p->getCanView()->contains($user)
									  && ! $p->getCanEdit()->contains($user)
									  && $p->getAuthor()?->getId() !== $user->getId()
		);
		/** @var Project $project */
		$project = $projects[array_rand($projects)];
		$route   = sprintf('/projects/%s/features', $project->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
	}
	
	public function testListDoesNotWorkForRegularUserWhoCannotViewProject(): void {
		/** @var RoleRepository $roleRepository */
		$roleRepository = IH::getRepository(static::$container, RoleRepository::class);
		/** @var Role $role */
		$role = $roleRepository->findOneBy(['name' => Role::ROLE_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRoles([$role]);
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = array_filter(
			$projectRepository->findAll(),
			static fn (Project $p) => ! $p->getSoftDeleted()
									  && $p->getFeatures()->count() !== 0
									  && ! $p->getCanView()->contains($user)
									  && ! $p->getCanEdit()->contains($user)
									  && $p->getAuthor()?->getId() !== $user->getId()
		);
		/** @var Project $project */
		$project = $projects[array_rand($projects)];
		$route   = sprintf('/projects/%s/features', $project->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
	}
	
	public function testListDoesNotWorkForAnon(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = array_filter(
			$projectRepository->findAll(),
			static fn (Project $p): bool => ! $p->getFeatures()->isEmpty() && ! $p->getSoftDeleted()
		);
		/** @var Project $project */
		$project = $projects[array_rand($projects)];
		$route   = sprintf('/projects/%s/features', $project->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
}