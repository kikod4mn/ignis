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
 * @covers \App\Controller\Category\DeleteController
 */
class DeleteControllerTest extends BaseWebTestCase {
	public function testSoftDelete(): void {
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = array_filter(
			$categoryRepository->findAll(),
			static fn (Category $category) => ! $category->getSoftDeleted()
		);
		$category           = $categories[array_rand($categories)];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_ADMIN]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = sprintf('/categories/%s/delete', $category->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertTrue($categoryRepository->find($category->getId())?->getSoftDeleted());
	}
	
	public function testDelete(): void {
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		IH::disableSoftDeleteFilter(static::$container);
		$categories = array_filter(
			$categoryRepository->findAll(),
			static fn (Category $category) => $category->getSoftDeleted()
		);
		$category   = $categories[array_rand($categories)];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_ADMIN]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = sprintf('/categories/%s/delete', $category->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertNull($categoryRepository->findOneBy(['uuid' => $category->getUuid()?->toString()]));
	}
	
	public function testDeleteForAnon(): void {
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = array_filter(
			$categoryRepository->findAll(),
			static fn (Category $category) => ! $category->getSoftDeleted()
		);
		$category           = $categories[array_rand($categories)];
		$route              = sprintf('/categories/%s/delete', $category->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testDeleteForTestUser(): void {
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = array_filter(
			$categoryRepository->findAll(),
			static fn (Category $category) => ! $category->getSoftDeleted()
		);
		$category           = $categories[array_rand($categories)];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_TEST_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = sprintf('/categories/%s/delete', $category->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertNotNull($categoryRepository->find($category->getId()));
	}
	
	public function testProjectLeadCannotDelete(): void {
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = array_filter(
			$categoryRepository->findAll(),
			static fn (Category $category) => ! $category->getSoftDeleted()
		);
		$category           = $categories[array_rand($categories)];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = sprintf('/categories/%s/delete', $category->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		static::assertNotNull($categoryRepository->find($category->getId()));
	}
}