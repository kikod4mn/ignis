<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Category;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Category\CreateController
 */
class CreateControllerTest extends BaseWebTestCase {
	public function testCreatePage(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $creator */
		$creator = $userRepository->findOneByRole(User::ROLE_ADMIN);
		$route   = '/categories/create';
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
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $creator */
		$creator = $userRepository->findOneByRole(User::ROLE_ADMIN);
		$route   = '/categories/create';
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
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $creator */
		$creator = $userRepository->findOneByRole(User::ROLE_TEST_USER);
		$route   = '/categories/create';
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
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $creator */
		$creator = $userRepository->findOneByRole(User::ROLE_PROJECT_LEAD);
		$route   = '/categories/create';
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$category           = $categoryRepository->findOneBy(['name' => $name]);
		static::assertNull($category);
	}
}