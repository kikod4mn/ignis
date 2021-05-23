<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Admin\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

class ShowControllerTest extends BaseWebTestCase {
	public function testShow(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $loggedUser */
		$loggedUser = $userRepository->findOneByRole(User::ROLE_ADMIN);
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
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $loggedUser */
		$loggedUser = $userRepository->findOneByRole(User::ROLE_TEST_USER);
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
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $loggedUser */
		$loggedUser = $userRepository->findOneByRole(User::ROLE_PROJECT_LEAD);
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
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $loggedUser */
		$loggedUser = $userRepository->findOneByRole(User::ROLE_USER);
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