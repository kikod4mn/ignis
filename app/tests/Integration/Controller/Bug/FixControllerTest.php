<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Bug;

use App\Entity\Bug;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\BugRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Bug\FixController
 */
class FixControllerTest extends BaseWebTestCase {
	public function testBugFix(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$projectLeads   = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u): bool {
				if ($u->getProjects()->count() === 0) {
					return false;
				}
				foreach ($u->getProjects() as $p) {
					if ($p->getBugs()->count() === 0) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $fixer */
		$fixer    = $projectLeads[array_rand($projectLeads)];
		$projects = array_filter(
			$fixer->getProjects()->toArray(),
			static fn (Project $p): bool => $p->getBugs()->count() > 5 && ! $p->getSoftDeleted()
		);
		/** @var Project $project */
		$project = $projects[array_rand($projects)];
		$bugs    = array_filter(
			$project->getBugs()->toArray(),
			static fn (Bug $b): bool => ! $b->isFixed() && ! $b->getSoftDeleted()
		);
		/** @var Bug $bug */
		$bug   = $bugs[array_rand($bugs)];
		$route = sprintf('/projects/%s/bugs/%s/fix', $project->getUuid()?->toString(), $bug->getUuid()?->toString());
		$this->getClient()->loginUser($fixer);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		static::assertTrue($bugRepository->find($bug->getId())?->isFixed());
	}
	
	public function testBugFixDoesWorkForProjectLeadWhoIsNotProjectAuthorButIsEditor(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b) => ! $b->isFixed()
								  && ! $b->getSoftDeleted()
								  && $b->getProject()?->getCanEdit()->count() > 2
		);
		/** @var Bug $bug */
		$bug = $bugs[array_rand($bugs)];
		/** @var Project $project */
		$project = $bug->getProject();
		$route   = sprintf('/projects/%s/bugs/%s/fix', $project->getUuid()?->toString(), $bug->getUuid()?->toString());
		/** @var User $fixer */
		$fixer = $bug->getProject()?->getCanEdit()->filter(static fn (User $u): bool => $u->getId() !== $bug->getProject()?->getAuthor()?->getId())->first();
		$this->getClient()->loginUser($fixer);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		static::assertTrue($bugRepository->find($bug->getId())?->isFixed());
	}
	
	public function testBugFixDoesWorkForProjectLeadWhoIsNotAuthorAndCannotEditProject(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $fixer */
		$fixer = $userRepository->findOneByRole(User::ROLE_PROJECT_LEAD);
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b) => ! $b->isFixed()
								  && ! $b->getSoftDeleted()
								  && $fixer->getId() !== $b->getProject()?->getAuthor()?->getId()
								  && ! $b->getProject()?->getCanEdit()->contains($fixer)
								  && ! $b->getProject()?->getCanView()->contains($fixer)
		);
		/** @var Bug $bug */
		$bug = $bugs[array_rand($bugs)];
		/** @var Project $project */
		$project = $bug->getProject();
		$route   = sprintf('/projects/%s/bugs/%s/fix', $project->getUuid()?->toString(), $bug->getUuid()?->toString());
		$this->getClient()->loginUser($fixer);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		static::assertFalse($bugRepository->find($bug->getId())?->isFixed());
	}
	
	public function testBugFixDoesNotWorkForBugAuthor(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b) => ! $b->isFixed() && ! $b->getSoftDeleted()
		);
		/** @var Bug $bug */
		$bug = $bugs[array_rand($bugs)];
		/** @var Project $project */
		$project = $bug->getProject();
		/** @var User $fixer */
		$fixer = $bug->getAuthor();
		$route = sprintf('/projects/%s/bugs/%s/fix', $project->getUuid()?->toString(), $bug->getUuid()?->toString());
		$this->getClient()->loginUser($fixer);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		static::assertFalse($bugRepository->find($bug->getId())?->isFixed());
	}
	
	public function testBugFixForTestUser(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b) => ! $b->isFixed() && ! $b->getSoftDeleted()
		);
		/** @var Bug $bug */
		$bug = $bugs[array_rand($bugs)];
		/** @var Project $project */
		$project = $bug->getProject();
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $fixer */
		$fixer = $userRepository->findOneByRole(User::ROLE_TEST_USER);
		$route = sprintf('/projects/%s/bugs/%s/fix', $project->getUuid()?->toString(), $bug->getUuid()?->toString());
		$this->getClient()->loginUser($fixer);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertFalse($bugRepository->find($bug->getId())?->isFixed());
	}
	
	public function testBugFixForRegularUser(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b) => ! $b->isFixed() && ! $b->getSoftDeleted()
		);
		/** @var Bug $bug */
		$bug = $bugs[array_rand($bugs)];
		/** @var Project $project */
		$project = $bug->getProject();
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $fixer */
		$fixer = $userRepository->findOneByRole(User::ROLE_USER);
		$route = sprintf('/projects/%s/bugs/%s/fix', $project->getUuid()?->toString(), $bug->getUuid()?->toString());
		$this->getClient()->loginUser($fixer);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		static::assertFalse($bugRepository->find($bug->getId())?->isFixed());
	}
	
	public function testBugFixForAnon(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = IH::getRepository(static::$container, BugRepository::class);
		$bugs          = array_filter(
			$bugRepository->findAll(),
			static fn (Bug $b) => ! $b->isFixed() && ! $b->getSoftDeleted()
		);
		/** @var Bug $bug */
		$bug = $bugs[array_rand($bugs)];
		/** @var Project $project */
		$project = $bug->getProject();
		$route   = sprintf('/projects/%s/bugs/%s/fix', $project->getUuid()?->toString(), $bug->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
		static::assertFalse($bugRepository->find($bug->getId())?->isFixed());
	}
}