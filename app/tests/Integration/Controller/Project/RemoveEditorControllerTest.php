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
 * @covers \App\Controller\Project\RemoveEditorController
 */
class RemoveEditorControllerTest extends BaseWebTestCase {
	public function testRemoveEditPage(): void {
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
		$route   = sprintf('/projects/%s/remove-can-edit/choose', $project->getUuid()?->toString());
		$this->getClient()->loginUser($author);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testRemoveEditPageForUser(): void {
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
		$route   = sprintf('/projects/%s/remove-can-edit/choose', $project->getUuid()?->toString());
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_USER);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
	}
	
	public function testRemoveEditPageForAnon(): void {
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
		$route   = sprintf('/projects/%s/remove-can-edit/choose', $project->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testRemoveEdit(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$authors        = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u): bool {
				if ($u->getProjects()->isEmpty()) {
					return false;
				}
				/** @var Project $project */
				foreach ($u->getProjects() as $project) {
					if ($project->getCanEdit()->isEmpty()) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $author */
		$author = $authors[array_rand($authors)];
		/** @var Project $project */
		$project = $author->getProjects()->filter(static fn (Project $p): bool => ! $p->getCanEdit()->isEmpty())->first();
		/** @var User $removeEditor */
		$removeEditor = $project->getCanEdit()->first();
		static::assertTrue($project->getCanEdit()->contains($removeEditor));
		$route = sprintf('/projects/%s/remove-can-edit/choose', $project->getUuid()?->toString());
		$data  = [
			'choose_user_remove_editor' => [
				'_canEdit' => [$removeEditor->getUuid()?->toString()],
				'_token'   => IH::getCsrf(static::$container)->getToken('_choose_user[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($author);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertFalse($project->getCanEdit()->contains($removeEditor));
	}
	
	public function testRemoveEditDoesNotWorkForProjectLeadWhoIsNotOwner(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$authors        = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u): bool {
				if ($u->getProjects()->isEmpty()) {
					return false;
				}
				/** @var Project $project */
				foreach ($u->getProjects() as $project) {
					if ($project->getCanEdit()->isEmpty()) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $author */
		$author = $authors[array_rand($authors)];
		/** @var Project $project */
		$project = $author->getProjects()->filter(static fn (Project $p): bool => ! $p->getCanEdit()->isEmpty())->first();
		/** @var User $removeEditor */
		$removeEditor = $project->getCanEdit()->first();
		static::assertTrue($project->getCanEdit()->contains($removeEditor));
		$route      = sprintf('/projects/%s/remove-can-edit/choose', $project->getUuid()?->toString());
		$data       = [
			'choose_user_remove_editor' => [
				'_canEdit' => [$removeEditor->getUuid()?->toString()],
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
		static::assertTrue($project->getCanEdit()->contains($removeEditor));
	}
	
	public function testRemoveEditDoesNotWorkForRegularUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$authors        = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u): bool {
				if ($u->getProjects()->isEmpty()) {
					return false;
				}
				/** @var Project $project */
				foreach ($u->getProjects() as $project) {
					if ($project->getCanEdit()->isEmpty()) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $author */
		$author = $authors[array_rand($authors)];
		/** @var Project $project */
		$project = $author->getProjects()->filter(static fn (Project $p): bool => ! $p->getCanEdit()->isEmpty())->first();
		/** @var User $removeEditor */
		$removeEditor = $project->getCanEdit()->first();
		static::assertTrue($project->getCanEdit()->contains($removeEditor));
		$route      = sprintf('/projects/%s/remove-can-edit/choose', $project->getUuid()?->toString());
		$data       = [
			'choose_user_remove_editor' => [
				'_canEdit' => [$removeEditor->getUuid()?->toString()],
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
		static::assertTrue($project->getCanEdit()->contains($removeEditor));
	}
	
	public function testRemoveEditDoesNotWorkForAnon(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$authors        = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u): bool {
				if ($u->getProjects()->isEmpty()) {
					return false;
				}
				/** @var Project $project */
				foreach ($u->getProjects() as $project) {
					if ($project->getCanEdit()->isEmpty()) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $author */
		$author = $authors[array_rand($authors)];
		/** @var Project $project */
		$project = $author->getProjects()->filter(static fn (Project $p): bool => ! $p->getCanEdit()->isEmpty())->first();
		/** @var User $removeEditor */
		$removeEditor = $project->getCanEdit()->first();
		static::assertTrue($project->getCanEdit()->contains($removeEditor));
		$route = sprintf('/projects/%s/remove-can-edit/choose', $project->getUuid()?->toString());
		$data  = [
			'choose_user_remove_editor' => [
				'_canEdit' => [$removeEditor->getUuid()?->toString()],
				'_token'   => IH::getCsrf(static::$container)->getToken('_choose_user[_csrf_token]'),
			],
		];
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertTrue($project->getCanEdit()->contains($removeEditor));
	}
}