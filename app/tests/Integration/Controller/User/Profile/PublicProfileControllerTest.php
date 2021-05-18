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
 * @covers \App\Controller\User\Profile\PublicProfileController
 */
class PublicProfileControllerTest extends BaseWebTestCase {
	public function testRegularUserCanSeeOtherUsers(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findBy(['active' => true]);
		$user           = $users[array_rand($users)];
		$usersToView    = array_filter($users, static fn (User $potentialToView) => $potentialToView->getId() !== $user->getId());
		$userToView     = $usersToView[array_rand($usersToView)];
		$this->getClient()->loginUser($user);
		$route = sprintf('/%s/profile', $userToView->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
		$responseText = (string) $this->getClient()->getResponse()->getContent();
		static::assertStringContainsStringIgnoringCase('PROFILE', $responseText);
		static::assertStringContainsStringIgnoringCase('ACTIVITY', $responseText);
	}
	
	public function testPublicProfileDoesntWorkForAnon(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$usersToView    = array_filter(
			$userRepository->findBy(['active' => true]),
			static fn (User $potentialToView) => $potentialToView->getActive()
		);
		$userToView     = $usersToView[array_rand($usersToView)];
		$route          = sprintf('/%s/profile', $userToView->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testTheTestUserCanNotSeeOtherUsers(): void {
		/** @var RoleRepository $roleRepository */
		$roleRepository = IH::getRepository(static::$container, RoleRepository::class);
		/** @var Role $userRole */
		$userRole = $roleRepository->findOneBy(['name' => Role::ROLE_USER]);
		/** @var Role $testUserRole */
		$testUserRole = $roleRepository->findOneBy(['name' => Role::ROLE_TEST_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $userToView */
		$userToView = $userRepository->findOneByRoles([$userRole]);
		/** @var User $user */
		$user = $userRepository->findOneByRoles([$testUserRole]);
		$this->getClient()->loginUser($user);
		$route = sprintf('/%s/profile', $userToView->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
		$responseText = (string) $this->getClient()->getResponse()->getContent();
		static::assertStringContainsStringIgnoringCase('PROFILE', $responseText);
		static::assertStringContainsStringIgnoringCase('ACTIVITY', $responseText);
	}
}