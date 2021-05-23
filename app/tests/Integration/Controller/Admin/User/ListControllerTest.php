<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Admin\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Admin\User\ListController
 */
class ListControllerTest extends BaseWebTestCase {
	public function testList(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_ADMIN);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/admin/users');
		static::assertResponseIsSuccessful();
	}
	
	public function testListWorksForTestUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_ADMIN);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/admin/users');
		static::assertResponseIsSuccessful();
	}
	
	public function testListDoesNotWorkForProjectLead(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_PROJECT_LEAD);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/admin/users');
		static::assertResponseStatusCodeSame(404);
	}
	
	public function testListDoesNotWorkForRegularUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_USER);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/admin/users');
		static::assertResponseStatusCodeSame(404);
	}
	
	public function testListDoesNotWorkForAnon(): void {
		$this->getClient()->request(Request::METHOD_GET, '/admin/users');
		static::assertResponseStatusCodeSame(404);
	}
}