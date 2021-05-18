<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Admin\Project;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \App\Controller\Admin\Project\ListController
 */
class ListControllerTest extends BaseWebTestCase {
	public function testList(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_ADMIN]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/admin/projects');
		static::assertResponseIsSuccessful();
	}
	
	public function testListWorksForTestUser(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_TEST_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/admin/projects');
		static::assertResponseIsSuccessful();
	}
	
	public function testListDoesNotWorkForRegularUser(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/admin/projects');
		static::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
	}
	
	public function testListDoesNotWorkForProjectLead(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRoles([$role]);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/admin/projects');
		static::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
	}
	
	public function testListDoesNotWorkForAnon(): void {
		$this->getClient()->request(Request::METHOD_GET, '/admin/projects');
		static::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
	}
}