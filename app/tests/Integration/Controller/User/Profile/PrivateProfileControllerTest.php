<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\User\Profile;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\User\Profile\PrivateProfileController
 */
class PrivateProfileControllerTest extends BaseWebTestCase {
	public function testPrivateProfile(): void {
		/** @var RoleRepository $roleRepository */
		$roleRepository = IH::getRepository(static::$container, RoleRepository::class);
		/** @var Role $role */
		$role = $roleRepository->findOneBy(['name' => Role::ROLE_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/profile');
		static::assertResponseIsSuccessful();
		$responseText = (string) $this->getClient()->getResponse()->getContent();
		static::assertStringContainsStringIgnoringCase('PROFILE', $responseText);
		static::assertStringContainsStringIgnoringCase('ACTIVITY', $responseText);
	}
	
	public function testPrivateProfileDoesntWorkForAnon(): void {
		$this->getClient()->request(Request::METHOD_GET, '/profile');
		static::assertResponseStatusCodeSame(302);
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testAccountPageExampleWorksForTestUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var RoleRepository $roleRepository */
		$roleRepository = IH::getRepository(static::$container, RoleRepository::class);
		/** @var Role $role */
		$role = $roleRepository->findOneBy(['name' => Role::ROLE_TEST_USER]);
		/** @var User $user */
		$user = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/profile');
		static::assertResponseIsSuccessful();
		$responseText = (string) $this->getClient()->getResponse()->getContent();
		static::assertStringContainsStringIgnoringCase('PROFILE', $responseText);
		static::assertStringContainsStringIgnoringCase('ACTIVITY', $responseText);
	}
}