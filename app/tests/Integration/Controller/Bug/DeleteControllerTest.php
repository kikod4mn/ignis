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
use DateTimeInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Bug\DeleteController
 */
class DeleteControllerTest extends BaseWebTestCase {
	public function testBugSoftDelete(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b) => ! $b->getSoftDeleted() && ! $b->getProject()?->getSoftDeleted()
		);
		$bug           = $bugs[array_rand($bugs)];
		static::assertFalse($bug->getSoftDeleted());
		/** @var Project $project */
		$project = $bug->getProject();
		/** @var User $author */
		$author = $bug->getAuthor();
		$route  = sprintf(
			'/projects/%s/bugs/%s/delete',
			$project->getUuid()?->toString(), $bug->getUuid()?->toString()
		);
		static::assertTrue($project->getBugs()->contains($bug));
		$this->getClient()->loginUser($author);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		IH::disableSoftDeleteFilter(static::$container);
		$bug = $bugRepository->find($bug->getId());
		static::assertNotNull($bug);
		static::assertSame($bug->getProject()?->getId(), $project->getId());
		static::assertTrue($bug->getSoftDeleted());
		static::assertNotNull($bug->getSoftDeletedAt());
	}
	
	public function testBugDelete(): void {
		IH::disableSoftDeleteFilter(static::$container);
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(), static fn (Bug $b) => $b->getSoftDeleted()
															 && $b->getSoftDeletedAt() instanceof DateTimeInterface
															 && ! $b->getProject()?->getSoftDeleted()
		);
		$bug           = $bugs[array_rand($bugs)];
		static::assertTrue($bug->getSoftDeleted());
		static::assertNotNull($bug->getSoftDeletedAt());
		/** @var Project $project */
		$project = $bug->getProject();
		/** @var User $author */
		$author = $bug->getAuthor();
		$route  = sprintf(
			'/projects/%s/bugs/%s/delete',
			$project->getUuid()?->toString(), $bug->getUuid()?->toString()
		);
		static::assertTrue($project->getBugs()->contains($bug));
		$this->getClient()->loginUser($author);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var Project $project */
		$project = IH::getRepository(static::$container, ProjectRepository::class)
					 ->findOneBy(['uuid' => $project->getUuid()?->toString()])
		;
		static::assertFalse($project->getBugs()->contains($bug));
		IH::disableSoftDeleteFilter(static::$container);
		/** @var ?Project $project */
		$project = IH::getRepository(static::$container, ProjectRepository::class)->find($project->getId());
		/** @var ?Bug $bug */
		$bug = $bugRepository->findOneBy(['title' => $bug->getTitle(), 'description' => $bug->getDescription()]);
		static::assertNotNull($project);
		static::assertNull($bug);
	}
	
	public function testBugDeleteForTestUser(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b) => ! $b->getSoftDeleted() && ! $b->getProject()?->getSoftDeleted()
		);
		$bug           = $bugs[array_rand($bugs)];
		/** @var Project $project */
		$project = $bug->getProject();
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $testUser */
		$testUser = $userRepository->findOneByRole(User::ROLE_TEST_USER);
		$route    = sprintf(
			'/projects/%s/bugs/%s/delete',
			$project->getUuid()?->toString(), $bug->getUuid()?->toString()
		);
		static::assertTrue($project->getBugs()->contains($bug));
		$this->getClient()->loginUser($testUser);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var Project $project */
		$project = IH::getRepository(static::$container, ProjectRepository::class)->find($project->getId());
		$bug     = $bugRepository->find($bug->getId());
		static::assertSame($bug?->getProject()?->getId(), $project->getId());
		static::assertInstanceOf(Bug::class, $bug);
	}
	
	public function testBugDeleteDoesNotWorkForNotAuthor(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(), static fn (Bug $b) => ! $b->getSoftDeleted()
															 && ! $b->getProject()?->getSoftDeleted()
		);
		$bug           = $bugs[array_rand($bugs)];
		/** @var Project $project */
		$project = $bug->getProject();
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRole(User::ROLE_USER),
			static fn (User $u): bool => ! $project->getCanView()->contains($u)
										 && ! $project->getCanEdit()->contains($u)
										 && $project->getAuthor()?->getId() !== $u->getId()
		);
		/** @var User $user */
		$user  = $users[array_rand($users)];
		$route = sprintf(
			'/projects/%s/bugs/%s/delete',
			$project->getUuid()?->toString(), $bug->getUuid()?->toString()
		);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		/** @var Project $project */
		$project = IH::getRepository(static::$container, ProjectRepository::class)->find($project->getId());
		/** @var ?Bug $bug */
		$bug = $bugRepository->find($bug->getId());
		static::assertSame($bug?->getProject()?->getId(), $project->getId());
		static::assertInstanceOf(Bug::class, $bug);
	}
	
	public function testBugDeleteDoesNotWorkForAnon(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b) => ! $b->getSoftDeleted() && ! $b->getProject()?->getSoftDeleted()
		);
		$bug           = $bugs[array_rand($bugs)];
		/** @var Project $project */
		$project = $bug->getProject();
		$route   = sprintf(
			'/projects/%s/bugs/%s/delete',
			$project->getUuid()?->toString(), $bug->getUuid()?->toString()
		);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
		/** @var Project $project */
		$project = IH::getRepository(static::$container, ProjectRepository::class)->find($project->getId());
		/** @var ?Bug $bug */
		$bug = IH::getRepository(static::$container, BugRepository::class)->find($bug->getId());
		static::assertSame($bug?->getProject()?->getId(), $project->getId());
		static::assertInstanceOf(Bug::class, $bug);
	}
}