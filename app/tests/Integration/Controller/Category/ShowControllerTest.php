<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Category;

use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \App\Controller\Category\ShowController
 */
class ShowControllerTest extends BaseWebTestCase {
	public function testShowWorksForAdmin(): void {
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		$category           = $categories[array_rand($categories)];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_ADMIN);
		$route = sprintf('/categories/%s/show', $category->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testShowWorksForTestUser(): void {
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		$category           = $categories[array_rand($categories)];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_TEST_USER);
		$route = sprintf('/categories/%s/show', $category->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testShowWorksForProjectLead(): void {
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		$category           = $categories[array_rand($categories)];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_PROJECT_LEAD);
		$route = sprintf('/categories/%s/show', $category->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testShowDoesNotWorkForRegularUser(): void {
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		$category           = $categories[array_rand($categories)];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_USER);
		$route = sprintf('/categories/%s/show', $category->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
	}
	
	public function testShowDoesNotWorkForAnon(): void {
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		$category           = $categories[array_rand($categories)];
		$route              = sprintf('/categories/%s/show', $category->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
}