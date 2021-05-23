<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Project;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Project\AddEditorController
 */
class AddEditorControllerTest extends BaseWebTestCase {
	public function testAddEditPage(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$authors        = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static fn (User $u): bool => ! $u->getProjects()->isEmpty()
		);
		/** @var User $author */
		$author = $authors[array_rand($authors)];
		/** @var Project $project */
		$project = $author->getProjects()->first();
		$route   = sprintf('/projects/%s/add-can-edit/choose', $project->getUuid()?->toString());
		$this->getClient()->loginUser($author);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testAddEditPageForUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$authors        = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static fn (User $u): bool => ! $u->getProjects()->isEmpty()
		);
		/** @var User $author */
		$author = $authors[array_rand($authors)];
		/** @var Project $project */
		$project = $author->getProjects()->first();
		$route   = sprintf('/projects/%s/add-can-edit/choose', $project->getUuid()?->toString());
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_USER);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
	}
	
	public function testAddEditPageForAnon(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$authors        = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static fn (User $u): bool => ! $u->getProjects()->isEmpty()
		);
		/** @var User $author */
		$author = $authors[array_rand($authors)];
		/** @var Project $project */
		$project = $author->getProjects()->first();
		$route   = sprintf('/projects/%s/add-can-edit/choose', $project->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testAddEdit(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$addEditors     = $userRepository->findByRole(User::ROLE_USER);
		$addEditor      = $addEditors[array_rand($addEditors)];
		$authors        = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u) use ($addEditor): bool {
				if ($u->getProjects()->isEmpty()) {
					return false;
				}
				/** @var Project $project */
				foreach ($u->getProjects() as $project) {
					if ($project->getCanEdit()->contains($addEditor)) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $author */
		$author = $authors[array_rand($authors)];
		/** @var Project $project */
		$project = $author->getProjects()->first();
		static::assertFalse($project->getCanEdit()->contains($addEditor));
		$route = sprintf('/projects/%s/add-can-edit/choose', $project->getUuid()?->toString());
		$data  = [
			'choose_user_add_editor' => [
				'_canEdit' => [$addEditor->getUuid()?->toString()],
				'_token'   => IH::getCsrf(static::$container)->getToken('_choose_user[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($author);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertTrue($project->getCanEdit()->contains($addEditor));
	}
	
	public function testAddEditDoesNotWorkForProjectLeadWhoIsNotOwner(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$addEditors     = $userRepository->findByRole(User::ROLE_USER);
		$addEditor      = $addEditors[array_rand($addEditors)];
		$authors        = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u) use ($addEditor): bool {
				if ($u->getProjects()->isEmpty()) {
					return false;
				}
				/** @var Project $project */
				foreach ($u->getProjects() as $project) {
					if ($project->getCanEdit()->contains($addEditor)) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $author */
		$author = $authors[array_rand($authors)];
		/** @var Project $project */
		$project = $author->getProjects()->first();
		static::assertFalse($project->getCanEdit()->contains($addEditor));
		$route      = sprintf('/projects/%s/add-can-edit/choose', $project->getUuid()?->toString());
		$data       = [
			'choose_user_add_editor' => [
				'_canEdit' => [$addEditor->getUuid()?->toString()],
				'_token'   => IH::getCsrf(static::$container)->getToken('_choose_user[_csrf_token]'),
			],
		];
		$notAuthors = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static fn (User $u): bool => $u->getId() !== $author->getId()
		);
		/** @var User $notAuthor */
		$notAuthor = $notAuthors[array_rand($notAuthors)];
		static::assertNotSame($notAuthor->getId(), $author->getId());
		$this->getClient()->loginUser($notAuthor);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		static::assertFalse($project->getCanEdit()->contains($addEditor));
	}
	
	public function testAddEditDoesNotWorkForRegularUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$addEditors     = $userRepository->findByRole(User::ROLE_USER);
		$addEditor      = $addEditors[array_rand($addEditors)];
		$authors        = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u) use ($addEditor): bool {
				if ($u->getProjects()->isEmpty()) {
					return false;
				}
				/** @var Project $project */
				foreach ($u->getProjects() as $project) {
					if ($project->getCanEdit()->contains($addEditor)) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $author */
		$author = $authors[array_rand($authors)];
		/** @var Project $project */
		$project = $author->getProjects()->first();
		static::assertFalse($project->getCanEdit()->contains($addEditor));
		$route      = sprintf('/projects/%s/add-can-edit/choose', $project->getUuid()?->toString());
		$data       = [
			'choose_user_add_editor' => [
				'_canEdit' => [$addEditor->getUuid()?->toString()],
				'_token'   => IH::getCsrf(static::$container)->getToken('_choose_user[_csrf_token]'),
			],
		];
		$notAuthors = array_filter(
			$userRepository->findByRole(User::ROLE_USER)
			, static fn (User $u): bool => $u->getId() !== $author->getId()
		);
		/** @var User $notAuthor */
		$notAuthor = $notAuthors[array_rand($notAuthors)];
		$this->getClient()->loginUser($notAuthor);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		static::assertFalse($project->getCanEdit()->contains($addEditor));
	}
	
	public function testAddEditDoesNotWorkForAnon(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$addEditors     = $userRepository->findByRole(User::ROLE_USER);
		$addEditor      = $addEditors[array_rand($addEditors)];
		$authors        = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u) use ($addEditor): bool {
				if ($u->getProjects()->isEmpty()) {
					return false;
				}
				/** @var Project $project */
				foreach ($u->getProjects() as $project) {
					if ($project->getCanEdit()->contains($addEditor)) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $author */
		$author = $authors[array_rand($authors)];
		/** @var Project $project */
		$project = $author->getProjects()->first();
		static::assertFalse($project->getCanEdit()->contains($addEditor));
		$route = sprintf('/projects/%s/add-can-edit/choose', $project->getUuid()?->toString());
		$data  = [
			'choose_user_add_editor' => [
				'_canEdit' => [$addEditor->getUuid()?->toString()],
				'_token'   => IH::getCsrf(static::$container)->getToken('_choose_user[_csrf_token]'),
			],
		];
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertFalse($project->getCanEdit()->contains($addEditor));
	}
}