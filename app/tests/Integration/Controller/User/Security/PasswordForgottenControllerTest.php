<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\User\Security;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

class PasswordForgottenControllerTest extends BaseWebTestCase {
	public function testPage(): void {
		$this->getClient()->request(Request::METHOD_GET, '/credentials/request/forgotten-password');
		static::assertResponseIsSuccessful();
	}
	
	public function testPageDoesNotWorkForUser(): void {
		/** @var RoleRepository $roleRepository */
		$roleRepository = IH::getRepository(static::$container, RoleRepository::class);
		/** @var Role $role */
		$role = $roleRepository->findOneBy(['name' => Role::ROLE_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u): bool => $u->getPasswordResetToken() === null
										 && $u->getPasswordResetTokenRequestedAt() === null
										 && $u->getPasswordResetTokenRequestedFromBrowser() === null
										 && $u->getPasswordResetTokenRequestedFromIp() === null
		);
		/** @var User $user */
		$user = $users[array_rand($users)];
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/credentials/request/forgotten-password');
		static::assertResponseStatusCodeSame(404);
	}
	
	public function testForm(): void {
		/** @var RoleRepository $roleRepository */
		$roleRepository = IH::getRepository(static::$container, RoleRepository::class);
		/** @var Role $role */
		$role = $roleRepository->findOneBy(['name' => Role::ROLE_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u): bool => $u->getPasswordResetToken() === null
										 && $u->getPasswordResetTokenRequestedAt() === null
										 && $u->getPasswordResetTokenRequestedFromBrowser() === null
										 && $u->getPasswordResetTokenRequestedFromIp() === null
		);
		/** @var User $user */
		$user = $users[array_rand($users)];
		$data = [
			'password_forgotten' => [
				'_email' => (string) $user->getEmail(),
				'_token' => IH::getCsrf(static::$container)->getToken('_password_forgotten[_csrf_token]'),
			],
		];
		$this->getClient()->request(Request::METHOD_POST, '/credentials/request/forgotten-password', $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertNotNull($user->getPasswordResetToken());
		static::assertNotNull($user->getPasswordResetTokenRequestedAt());
		static::assertNotNull($user->getPasswordResetTokenRequestedFromBrowser());
		static::assertNotNull($user->getPasswordResetTokenRequestedFromIp());
	}
	
	public function testFormIsSuccessfulForNonExistingUser(): void {
		$data = [
			'password_forgotten' => [
				'_email' => 'this-email-is-not@email.wtf',
				'_token' => IH::getCsrf(static::$container)->getToken('_password_forgotten[_csrf_token]'),
			],
		];
		$this->getClient()->request(Request::METHOD_POST, '/credentials/request/forgotten-password', $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
	}
}