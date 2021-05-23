<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Bug;

use App\Entity\Bug;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\BugRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Bug\CreateController
 */
class CreateControllerTest extends BaseWebTestCase {
	public function testCreatePage(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findAll();
		$project           = $projects[array_rand($projects)];
		$users             = array_filter($project->getCanView()->toArray(), static fn (User $u) => $u->getActive());
		/** @var User $creator */
		$creator = $users[array_rand($users)];
		$route   = sprintf('/projects/%s/bugs/create', $project->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testCreatePageForAnon(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findAll();
		$project           = $projects[array_rand($projects)];
		$route             = sprintf('/projects/%s/bugs/create', $project->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testCreate(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findAll();
		$project           = $projects[array_rand($projects)];
		$users             = array_filter($project->getCanView()->toArray(), static fn (User $u) => $u->getActive());
		/** @var User $creator */
		$creator     = $users[array_rand($users)];
		$route       = sprintf('/projects/%s/bugs/create', $project->getUuid()?->toString());
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'bug_create' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_bug_create[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		/** @var ?Bug $bug */
		$bug = $bugRepository->findOneBy(['title' => $title, 'description' => $description]);
		static::assertInstanceOf(Bug::class, $bug);
	}
	
	public function testCreateForTestUser(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findAll();
		$project           = $projects[array_rand($projects)];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $creator */
		$creator     = $userRepository->findOneByRole(User::ROLE_TEST_USER);
		$route       = sprintf('/projects/%s/bugs/create', $project->getUuid()?->toString());
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'bug_create' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_bug_create[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseIsSuccessful();
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		/** @var ?Bug $bug */
		$bug = $bugRepository->findOneBy(['title' => $title, 'description' => $description]);
		static::assertNull($bug);
	}
	
	public function testUserCannotCreateForProjectHeCannotView(): void {
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = $projectRepository->findAll();
		$project           = $projects[array_rand($projects)];
		$users             = array_filter($project->getCanView()->toArray(), static fn (User $u) => $u->getActive());
		/** @var User $creator */
		$creator = $users[array_rand($users)];
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = array_filter(
			$projectRepository->findAll()
			, static fn (Project $p) => ! $p->getCanView()->contains($creator)
										&& ! $p->getCanEdit()->contains($creator)
										&& $p->getAuthor()?->getId() !== $creator->getId()
		);
		/** @var Project $project */
		$project     = $projects[array_rand($projects)];
		$route       = sprintf('/projects/%s/bugs/create', $project->getUuid()?->toString());
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'bug_create' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_bug_create[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		/** @var ?Bug $bug */
		$bug = $bugRepository->findOneBy(['title' => $title, 'description' => $description]);
		static::assertNull($bug);
	}
	
	public function testProjectLeadCannotCreateForProjectHeCannotEdit(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $creator */
		$creator = $userRepository->findOneByRole(User::ROLE_PROJECT_LEAD);
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		$projects          = array_filter(
			$projectRepository->findAll()
			, static fn (Project $p) => ! $p->getCanView()->contains($creator)
										&& ! $p->getCanEdit()->contains($creator)
										&& $p->getAuthor()?->getId() !== $creator->getId()
		);
		$project           = $projects[array_rand($projects)];
		$route             = sprintf('/projects/%s/bugs/create', $project->getUuid()?->toString());
		$title             = $this->getFaker()->sentence;
		$description       = $this->getFaker()->paragraph;
		$data              = [
			'bug_create' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_bug_create[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		/** @var ?Bug $bug */
		$bug = $bugRepository->findOneBy(['title' => $title, 'description' => $description]);
		static::assertNull($bug);
	}
}