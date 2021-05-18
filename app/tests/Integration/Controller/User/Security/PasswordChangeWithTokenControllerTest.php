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

class PasswordChangeWithTokenControllerTest extends BaseWebTestCase {
	public function testPage(): void {
		/** @var RoleRepository $roleRepository */
		$roleRepository = IH::getRepository(static::$container, RoleRepository::class);
		/** @var Role $role */
		$role = $roleRepository->findOneBy(['name' => Role::ROLE_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u): bool => $u->getPasswordResetToken() !== null
		);
		/** @var User $user */
		$user  = $users[array_rand($users)];
		$route = sprintf('/credentials/change-password/%s', (string) $user->getPasswordResetToken());
		$this->getClient()->request(Request::METHOD_GET, $route);
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
			static fn (User $u): bool => $u->getPasswordResetToken() !== null
		);
		/** @var User $user */
		$user  = $users[array_rand($users)];
		$route = sprintf('/credentials/change-password/%s', (string) $user->getPasswordResetToken());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
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
			static fn (User $u): bool => $u->getPasswordResetToken() !== null
		);
		/** @var User $user */
		$user       = $users[array_rand($users)];
		$oldPwdHash = $user->getPassword();
		$newPwd     = 'SuperStaticSecret1@###';
		$route      = sprintf('/credentials/change-password/%s', (string) $user->getPasswordResetToken());
		$data       = [
			'password_change_with_token' => [
				'_password' => $newPwd,
				'_token'    => IH::getCsrf(static::$container)->getToken('_password_change_with_token[_csrf_token]'),
			],
		];
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertContains($oldPwdHash, $user->getOldPasswordHashes());
		static::assertNotEquals($oldPwdHash, $user->getPassword());
		static::assertNotEquals($newPwd, $user->getPassword());
		static::assertNull($user->getPasswordResetToken());
	}
}