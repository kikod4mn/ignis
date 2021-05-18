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

class ShowControllerTest extends BaseWebTestCase {
	public function testShow(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_ADMIN]);
		/** @var userRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $loggedUser */
		$loggedUser = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($loggedUser);
		$users = array_filter(
			$userRepository->findAll(),
			static fn (User $user): bool => $user->getActive() === true
		);
		$user  = $users[array_rand($users)];
		$route = sprintf('/admin/users/%s/show', $user->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testShowDoesWorkForTestUser(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_TEST_USER]);
		/** @var userRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $loggedUser */
		$loggedUser = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($loggedUser);
		$users = array_filter(
			$userRepository->findAll(),
			static fn (User $user): bool => $user->getActive() === true
		);
		$user  = $users[array_rand($users)];
		$route = sprintf('/admin/users/%s/show', $user->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testShowDoesNotWorkForProjectLead(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var userRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $loggedUser */
		$loggedUser = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($loggedUser);
		$users = array_filter(
			$userRepository->findAll(),
			static fn (User $user): bool => $user->getActive() === true
		);
		$user  = $users[array_rand($users)];
		$route = sprintf('/admin/users/%s/show', $user->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
	}
	
	public function testShowDoesNotWorkForRegularUser(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_USER]);
		/** @var userRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $loggedUser */
		$loggedUser = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($loggedUser);
		$users = array_filter(
			$userRepository->findAll(),
			static fn (User $user): bool => $user->getActive() === true
		);
		$user  = $users[array_rand($users)];
		$route = sprintf('/admin/users/%s/show', $user->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
	}
	
	public function testShowDoesNotWorkForAnon(): void {
		/** @var userRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findAll(),
			static fn (User $user): bool => $user->getActive() === true
		);
		$user           = $users[array_rand($users)];
		$route          = sprintf('/admin/users/%s/show', $user->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
	}
}