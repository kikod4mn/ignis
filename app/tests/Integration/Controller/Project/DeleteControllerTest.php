<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Project;

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
 * @covers \App\Controller\Project\DeleteController
 */
class DeleteControllerTest extends BaseWebTestCase {
	public function testSoftDelete(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findBy(['softDeleted' => false]);
		$project           = $projects[array_rand($projects)];
		/** @var User $deletor */
		$deletor = $project->getAuthor();
		$route   = sprintf('/projects/%s/delete', $project->getUuid()?->toString());
		$this->getClient()->loginUser($deletor);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertTrue($project->getSoftDeleted());
		IH::disableSoftDeleteFilter(static::$container);
		$project = $projectRepository->findOneBy(['uuid' => $project->getUuid()?->toString()]);
		static::assertNotNull($project);
		static::assertTrue($project->getSoftDeleted());
		static::assertNotNull($project->getSoftDeletedAt());
	}
	
	public function testDelete(): void {
		IH::disableSoftDeleteFilter(static::$container);
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = array_filter(
			$projectRepository->findAll(),
			static fn (Project $p) => $p->getSoftDeleted()
		);
		$project           = $projects[array_rand($projects)];
		/** @var User $deletor */
		$deletor = $project->getAuthor();
		$route   = sprintf('/projects/%s/delete', $project->getUuid()?->toString());
		$this->getClient()->loginUser($deletor);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		IH::disableSoftDeleteFilter(static::$container);
		$project = $projectRepository->findOneBy(['uuid' => $project->getUuid()?->toString()]);
		static::assertNull($project);
	}
	
	public function testNotAuthorCantDelete(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findBy(['softDeleted' => false]);
		$project           = $projects[array_rand($projects)];
		/** @var RoleRepository $roleRepository */
		$roleRepository = IH::getRepository(static::$container, RoleRepository::class);
		/** @var Role $role */
		$role = $roleRepository->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$deletors       = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u) => $u->getId() !== $project->getAuthor()?->getId()
		);
		/** @var User $deletor */
		$deletor = $deletors[array_rand($deletors)];
		static::assertNotSame($deletor->getId(), $project->getAuthor()?->getId());
		$route = sprintf('/projects/%s/delete', $project->getUuid()?->toString());
		$this->getClient()->loginUser($deletor);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		IH::disableSoftDeleteFilter(static::$container);
		$project = $projectRepository->findOneBy(['uuid' => $project->getUuid()?->toString()]);
		static::assertNotNull($project);
		static::assertFalse($project->getSoftDeleted());
		static::assertNull($project->getSoftDeletedAt());
	}
	
	public function testTestUserDelete(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findBy(['softDeleted' => false]);
		$project           = $projects[array_rand($projects)];
		/** @var RoleRepository $roleRepository */
		$roleRepository = IH::getRepository(static::$container, RoleRepository::class);
		/** @var Role $role */
		$role = $roleRepository->findOneBy(['name' => Role::ROLE_TEST_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$deletors       = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u) => $u->getId() !== $project->getAuthor()?->getId()
		);
		/** @var User $deletor */
		$deletor = $deletors[array_rand($deletors)];
		$route   = sprintf('/projects/%s/delete', $project->getUuid()?->toString());
		$this->getClient()->loginUser($deletor);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		IH::disableSoftDeleteFilter(static::$container);
		$project = $projectRepository->findOneBy(['uuid' => $project->getUuid()?->toString()]);
		static::assertNotNull($project);
		static::assertFalse($project->getSoftDeleted());
		static::assertNull($project->getSoftDeletedAt());
	}
	
	public function testAnonCantDelete(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findBy(['softDeleted' => false]);
		$project           = $projects[array_rand($projects)];
		$route             = sprintf('/projects/%s/delete', $project->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
		IH::disableSoftDeleteFilter(static::$container);
		$project = $projectRepository->findOneBy(['uuid' => $project->getUuid()?->toString()]);
		static::assertNotNull($project);
		static::assertFalse($project->getSoftDeleted());
		static::assertNull($project->getSoftDeletedAt());
	}
	
	public function testUserCantDelete(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findBy(['softDeleted' => false]);
		$project           = $projects[array_rand($projects)];
		/** @var RoleRepository $roleRepository */
		$roleRepository = IH::getRepository(static::$container, RoleRepository::class);
		/** @var Role $role */
		$role = $roleRepository->findOneBy(['name' => Role::ROLE_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$deletors       = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u) => $u->getId() !== $project->getAuthor()?->getId()
		);
		/** @var User $deletor */
		$deletor = $deletors[array_rand($deletors)];
		$route   = sprintf('/projects/%s/delete', $project->getUuid()?->toString());
		$this->getClient()->loginUser($deletor);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		IH::disableSoftDeleteFilter(static::$container);
		$project = $projectRepository->findOneBy(['uuid' => $project->getUuid()?->toString()]);
		static::assertNotNull($project);
		static::assertFalse($project->getSoftDeleted());
		static::assertNull($project->getSoftDeletedAt());
	}
}