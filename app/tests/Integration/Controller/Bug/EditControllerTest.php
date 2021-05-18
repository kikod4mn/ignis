<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Bug;

use App\Entity\Bug;
use App\Entity\Project;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\BugRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Bug\EditController
 */
class EditControllerTest extends BaseWebTestCase {
	public function testEditPage(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b) => ! $b->getSoftDeleted() && ! $b->getProject()?->getSoftDeleted()
		);
		$bug           = $bugs[array_rand($bugs)];
		/** @var User $creator */
		$creator = $bug->getAuthor();
		$project = $bug->getProject();
		$route   = sprintf('/projects/%s/bugs/%s/edit', $project?->getUuid()?->toString(), $bug->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testEditPageForAnon(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b) => ! $b->getSoftDeleted() && ! $b->getProject()?->getSoftDeleted()
		);
		$bug           = $bugs[array_rand($bugs)];
		$project       = $bug->getProject();
		$route         = sprintf('/projects/%s/bugs/%s/edit', $project?->getUuid()?->toString(), $bug->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testEdit(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b) => ! $b->getSoftDeleted() && ! $b->getProject()?->getSoftDeleted()
		);
		$bug           = $bugs[array_rand($bugs)];
		$project       = $bug->getProject();
		/** @var User $creator */
		$creator     = $bug->getAuthor();
		$route       = sprintf('/projects/%s/bugs/%s/edit', $project?->getUuid()?->toString(), $bug->getUuid()?->toString());
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'bug_edit' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_bug_edit[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var ?Bug $bug */
		$bug = $bugRepository->findOneBy(['title' => $title, 'description' => $description]);
		static::assertInstanceOf(Bug::class, $bug);
	}
	
	public function testEditForTestUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_TEST_USER]);
		/** @var User $creator */
		$creator = $userRepository->findOneByRoles([$role]);
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b) => ! $b->getSoftDeleted() && ! $b->getProject()?->getSoftDeleted()
		);
		$bug           = $bugs[array_rand($bugs)];
		$project       = $bug->getProject();
		$route         = sprintf('/projects/%s/bugs/%s/edit', $project?->getUuid()?->toString(), $bug->getUuid()?->toString());
		$title         = $this->getFaker()->sentence;
		$description   = $this->getFaker()->paragraph;
		$data          = [
			'bug_edit' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_bug_edit[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseIsSuccessful();
		/** @var ?Bug $bug */
		$bug = $bugRepository->findOneBy(['title' => $title, 'description' => $description]);
		static::assertNull($bug);
	}
	
	public function testUserCannotEditForProjectHeCannotView(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_USER]);
		/** @var User $creator */
		$creator = $userRepository->findOneByRoles([$role]);
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b): bool => ! $creator->getEditableProjects()->contains($b->getProject())
										&& ! $creator->getViewableProjects()->contains($b->getProject())
										&& $creator->getId() !== $b->getAuthor()?->getId()
										&& $creator->getId() !== $b->getProject()?->getAuthor()?->getId()
		);
		$bug           = $bugs[array_rand($bugs)];
		$project       = $bug->getProject();
		$route         = sprintf('/projects/%s/bugs/%s/edit', $project?->getUuid()?->toString(), $bug->getUuid()?->toString());
		$title         = $this->getFaker()->sentence;
		$description   = $this->getFaker()->paragraph;
		$data          = [
			'bug_edit' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_bug_edit[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		static::assertNull($bugRepository->findOneBy(['title' => $title, 'description' => $description]));
	}
	
	public function testProjectLeadCannotEditForProjectHeCannotEdit(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var User $creator */
		$creator = $userRepository->findOneByRoles([$role]);
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b): bool => ! $creator->getEditableProjects()->contains($b->getProject())
										&& ! $creator->getViewableProjects()->contains($b->getProject())
										&& $creator->getId() !== $b->getAuthor()?->getId()
										&& $creator->getId() !== $b->getProject()?->getAuthor()?->getId()
		);
		$bug           = $bugs[array_rand($bugs)];
		$project       = $bug->getProject();
		$route         = sprintf('/projects/%s/bugs/%s/edit', $project?->getUuid()?->toString(), $bug->getUuid()?->toString());
		$title         = $this->getFaker()->sentence;
		$description   = $this->getFaker()->paragraph;
		$data          = [
			'bug_edit' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_bug_edit[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		static::assertNull($bugRepository->findOneBy(['title' => $title, 'description' => $description]));
	}
	
	public function testEditForAnon(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b) => ! $b->getSoftDeleted() && ! $b->getProject()?->getSoftDeleted()
		);
		$bug           = $bugs[array_rand($bugs)];
		$project       = $bug->getProject();
		$route         = sprintf('/projects/%s/bugs/%s/edit', $project?->getUuid()?->toString(), $bug->getUuid()?->toString());
		$title         = $this->getFaker()->sentence;
		$description   = $this->getFaker()->paragraph;
		$data          = [
			'bug_edit' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_bug_edit[_csrf_token]'),
			],
		];
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
		/** @var ?Bug $bug */
		$bug = $bugRepository->findOneBy(['title' => $title, 'description' => $description]);
		static::assertNull($bug);
	}
}