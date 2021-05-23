<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Admin\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Admin\User\ActivateController
 */
class ActivateControllerTest extends BaseWebTestCase {
	public function testActivate(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_ADMIN);
		$this->getClient()->loginUser($user);
		$users = array_filter(
			$userRepository->findAll(),
			static fn (User $user): bool => ! $user->getActive()
		);
		$user  = $users[array_rand($users)];
		$route = sprintf('/admin/users/%s/activate', $user->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $activeUser */
		$activeUser = $userRepository->find($user->getId());
		static::assertSame($user->getUuid()?->toString(), $activeUser->getUuid()?->toString());
		static::assertTrue($activeUser->getActive());
	}
	
	public function testActivateDoesNotWorkForTestUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_TEST_USER);
		$this->getClient()->loginUser($user);
		$users = array_filter(
			$userRepository->findAll(),
			static fn (User $user): bool => ! $user->getActive()
		);
		$user  = $users[array_rand($users)];
		$route = sprintf('/admin/users/%s/activate', $user->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $activeUser */
		$activeUser = $userRepository->find($user->getId());
		static::assertSame($user->getUuid()?->toString(), $activeUser->getUuid()?->toString());
		static::assertFalse($activeUser->getActive());
	}
	
	public function testActivateDoesNotWorkForProjectLead(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_PROJECT_LEAD);
		$this->getClient()->loginUser($user);
		$users = array_filter(
			$userRepository->findAll(),
			static fn (User $user): bool => ! $user->getActive()
		);
		$user  = $users[array_rand($users)];
		$route = sprintf('/admin/users/%s/activate', $user->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $activeUser */
		$activeUser = $userRepository->find($user->getId());
		static::assertSame($user->getUuid()?->toString(), $activeUser->getUuid()?->toString());
		static::assertFalse($activeUser->getActive());
	}
	
	public function testActivateDoesNotWorkForUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_USER);
		$this->getClient()->loginUser($user);
		$users = array_filter(
			$userRepository->findAll(),
			static fn (User $user): bool => ! $user->getActive()
		);
		$user  = $users[array_rand($users)];
		$route = sprintf('/admin/users/%s/activate', $user->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $activeUser */
		$activeUser = $userRepository->find($user->getId());
		static::assertSame($user->getUuid()?->toString(), $activeUser->getUuid()?->toString());
		static::assertFalse($activeUser->getActive());
	}
	
	public function testActivateDoesNotWorkForAnon(): void {
		/** @var userRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findAll(),
			static fn (User $user): bool => ! $user->getActive()
		);
		$user           = $users[array_rand($users)];
		$route          = sprintf('/admin/users/%s/activate', $user->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $activeUser */
		$activeUser = $userRepository->find($user->getId());
		static::assertSame($user->getUuid()?->toString(), $activeUser->getUuid()?->toString());
		static::assertFalse($activeUser->getActive());
	}
}