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

class EditControllerTest extends BaseWebTestCase {
	public function testEditPage(): void {
		/** @var RoleRepository $roleRepository */
		$roleRepository = IH::getRepository(static::$container, RoleRepository::class);
		/** @var Role $role */
		$role = $roleRepository->findOneBy(['name' => Role::ROLE_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/profile/edit');
		static::assertResponseIsSuccessful();
	}
	
	public function testEditSubmission(): void {
		/** @var RoleRepository $roleRepository */
		$roleRepository = IH::getRepository(static::$container, RoleRepository::class);
		/** @var Role $role */
		$role = $roleRepository->findOneBy(['name' => Role::ROLE_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user    = $userRepository->findOneByRoles([$role]);
		$newName = $this->getFaker()->name;
		$oldName = $user->getName();
		static::assertNotSame($oldName, $newName);
		$data = [
			'edit' => [
				'_name'  => $newName,
				'_token' => IH::getCsrf(static::$container)->getToken('_profile_edit[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_POST, '/profile/edit', $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertSame($user->getName(), $newName);
	}
}