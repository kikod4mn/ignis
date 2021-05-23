<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Project;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Project\ListController
 */
class ListControllerTest extends BaseWebTestCase {
	public function testListWorksForAdmin(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_ADMIN);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/projects');
		static::assertResponseIsSuccessful();
	}
	
	public function testListWorksForTestUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_TEST_USER);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/projects');
		static::assertResponseIsSuccessful();
	}
	
	public function testListWorksForProjectLead(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_PROJECT_LEAD);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/projects');
		static::assertResponseIsSuccessful();
	}
	
	public function testListWorksForRegularUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_USER);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/projects');
		static::assertResponseIsSuccessful();
	}
	
	public function testListDoesNotWorkForAnon(): void {
		$this->getClient()->request(Request::METHOD_GET, '/projects');
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
}