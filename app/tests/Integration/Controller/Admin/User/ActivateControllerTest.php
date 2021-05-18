<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Admin\User;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Admin\User\ActivateController
 */
class ActivateControllerTest extends BaseWebTestCase {
	public function testActivate(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_ADMIN]);
		/** @var userRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($user);
		$users = array_filter(
			$userRepository->findAll(),
			static fn (User $user): bool => ! $user->getActive()
											&& $user->getEmailConfirmToken() === null
											&& $user->getEmailConfirmedAt() !== null
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
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_TEST_USER]);
		/** @var userRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($user);
		$users = array_filter(
			$userRepository->findAll(),
			static fn (User $user): bool => ! $user->getActive()
											&& $user->getEmailConfirmToken() === null
											&& $user->getEmailConfirmedAt() !== null
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
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var userRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($user);
		$users = array_filter(
			$userRepository->findAll(),
			static fn (User $user): bool => ! $user->getActive()
											&& $user->getEmailConfirmToken() === null
											&& $user->getEmailConfirmedAt() !== null
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
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_USER]);
		/** @var userRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($user);
		$users = array_filter(
			$userRepository->findAll(),
			static fn (User $user): bool => ! $user->getActive()
											&& $user->getEmailConfirmToken() === null
											&& $user->getEmailConfirmedAt() !== null
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
											&& $user->getEmailConfirmToken() === null
											&& $user->getEmailConfirmedAt() !== null
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