<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Category;

use App\Entity\Category;
use App\Entity\Role;
use App\Repository\CategoryRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Category\CreateController
 */
class CreateControllerTest extends BaseWebTestCase {
	public function testCreatePage(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_ADMIN]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = '/categories/create';
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testCreatePageForAnon(): void {
		$route = '/categories/create';
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testCreate(): void {
		$name = $this->getFaker()->sentence;
		$data = [
			'category_create' => [
				'_name'  => $name,
				'_token' => IH::getCsrf(static::$container)->getToken('_category_create[_csrf_token]'),
			],
		];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_ADMIN]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = '/categories/create';
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$category           = $categoryRepository->findOneBy(['name' => $name]);
		static::assertInstanceOf(Category::class, $category);
	}
	
	public function testCreateForTestUser(): void {
		$name = $this->getFaker()->sentence;
		$data = [
			'category_create' => [
				'_name'  => $name,
				'_token' => IH::getCsrf(static::$container)->getToken('_category_create[_csrf_token]'),
			],
		];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_TEST_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = '/categories/create';
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$category           = $categoryRepository->findOneBy(['name' => $name]);
		static::assertNull($category);
	}
	
	public function testProjectLeadCannotCreate(): void {
		$name = $this->getFaker()->sentence;
		$data = [
			'category_create' => [
				'_name'  => $name,
				'_token' => IH::getCsrf(static::$container)->getToken('_category_create[_csrf_token]'),
			],
		];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = '/categories/create';
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$category           = $categoryRepository->findOneBy(['name' => $name]);
		static::assertNull($category);
	}
}