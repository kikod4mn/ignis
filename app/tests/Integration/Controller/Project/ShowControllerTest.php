<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Project;

use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Project\ShowController
 */
class ShowControllerTest extends BaseWebTestCase {
	public function testShowWorksForAdmin(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findAll();
		$project           = $projects[array_rand($projects)];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user  = $userRepository->findOneByRole(User::ROLE_ADMIN);
		$route = sprintf('/projects/%s/show', $project->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testShowWorksForTestUser(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findAll();
		$project           = $projects[array_rand($projects)];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user  = $userRepository->findOneByRole(User::ROLE_TEST_USER);
		$route = sprintf('/projects/%s/show', $project->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testShowWorksForProjectLeadWhoIsEditor(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findAll();
		$project           = $projects[array_rand($projects)];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static fn (User $u): bool => $project->getCanEdit()->contains($u)
		);
		/** @var User $user */
		$user  = $users[array_rand($users)];
		$route = sprintf('/projects/%s/show', $project->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testShowDoesNotWorkForProjectLeadWhoIsNotEditor(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findAll();
		$project           = $projects[array_rand($projects)];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static fn (User $u): bool => $u->getId() !== $project->getAuthor()?->getId()
										 && ! $project->getCanEdit()->contains($u)
										 && ! $project->getCanView()->contains($u)
		);
		/** @var User $user */
		$user  = $users[array_rand($users)];
		$route = sprintf('/projects/%s/show', $project->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
	}
	
	public function testShowWorksForRegularUserWhoIsViewer(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findAll();
		$project           = $projects[array_rand($projects)];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRole(User::ROLE_USER),
			static fn (User $u): bool => $project->getCanView()->contains($u)
		);
		/** @var User $user */
		$user  = $users[array_rand($users)];
		$route = sprintf('/projects/%s/show', $project->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testShowDoesNotWorkForRegularUserThatIsNotViewer(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findAll();
		$project           = $projects[array_rand($projects)];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRole(User::ROLE_USER),
			static fn (User $u): bool => $u->getId() !== $project->getAuthor()?->getId()
										 && ! $project->getCanEdit()->contains($u)
										 && ! $project->getCanView()->contains($u)
		);
		/** @var User $user */
		$user  = $users[array_rand($users)];
		$route = sprintf('/projects/%s/show', $project->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
	}
	
	public function testShowDoesNotWorkForAnon(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findAll();
		$project           = $projects[array_rand($projects)];
		$route             = sprintf('/projects/%s/show', $project->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
}