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
 * @covers \App\Controller\Category\EditController
 */
class EditControllerTest extends BaseWebTestCase {
	public function testEditPage(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $creator */
		$creator = $userRepository->findOneByRole(User::ROLE_ADMIN);
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		$category           = $categories[array_rand($categories)];
		$route              = sprintf('/categories/%s/edit', $category->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testEditPageForAnon(): void {
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		$category           = $categories[array_rand($categories)];
		$route              = sprintf('/categories/%s/edit', $category->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testEdit(): void {
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		$category           = $categories[array_rand($categories)];
		$name               = $this->getFaker()->sentence;
		$data               = [
			'category_edit' => [
				'_name'  => $name,
				'_token' => IH::getCsrf(static::$container)->getToken('_category_edit[_csrf_token]'),
			],
		];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $creator */
		$creator = $userRepository->findOneByRole(User::ROLE_ADMIN);
		$route   = sprintf('/categories/%s/edit', $category->getUuid()?->toString());
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
	
	public function testEditForTestUser(): void {
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		$category           = $categories[array_rand($categories)];
		$name               = $this->getFaker()->sentence;
		$data               = [
			'category_edit' => [
				'_name'  => $name,
				'_token' => IH::getCsrf(static::$container)->getToken('_category_edit[_csrf_token]'),
			],
		];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $creator */
		$creator = $userRepository->findOneByRole(User::ROLE_TEST_USER);
		$route   = sprintf('/categories/%s/edit', $category->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseIsSuccessful();
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$category           = $categoryRepository->findOneBy(['name' => $name]);
		static::assertNull($category);
	}
	
	public function testProjectLeadCannotEdit(): void {
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		$category           = $categories[array_rand($categories)];
		$name               = $this->getFaker()->sentence;
		$data               = [
			'category_edit' => [
				'_name'  => $name,
				'_token' => IH::getCsrf(static::$container)->getToken('_category_edit[_csrf_token]'),
			],
		];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $creator */
		$creator = $userRepository->findOneByRole(User::ROLE_PROJECT_LEAD);
		$route   = sprintf('/categories/%s/edit', $category->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$category           = $categoryRepository->findOneBy(['name' => $name]);
		static::assertNull($category);
	}
}