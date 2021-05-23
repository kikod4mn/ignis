<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Project;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Project\CreateController
 */
class CreateControllerTest extends BaseWebTestCase {
	public function testCreatePage(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRole(User::ROLE_PROJECT_LEAD);
		$creator        = $users[array_rand($users)];
		$route          = '/projects/create';
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testCreatePageForAnon(): void {
		$route = '/projects/create';
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testCreatePageForRegularUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRole(User::ROLE_USER);
		$creator        = $users[array_rand($users)];
		$route          = '/projects/create';
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
	}
	
	public function testCreate(): void {
		/** @var UserRepository $userRepository */
		$userRepository     = IH::getRepository(static::$container, UserRepository::class);
		$users              = $userRepository->findByRole(User::ROLE_PROJECT_LEAD);
		$creator            = $users[array_rand($users)];
		$route              = '/projects/create';
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		/** @var Category $category */
		$category    = $categories[array_rand($categories)];
		$name        = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'project_create' => [
				'_name'        => $name,
				'_description' => $description,
				'_category'    => $category->getId(),
				'_token'       => IH::getCsrf(static::$container)->getToken('_project_create[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertNotNull(
			IH::getRepository(static::$container, ProjectRepository::class)
			  ->findOneBy(compact('name', 'description', 'category'))
		);
	}
	
	public function testCreateForTestUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository     = IH::getRepository(static::$container, UserRepository::class);
		$users              = $userRepository->findByRole(User::ROLE_TEST_USER);
		$creator            = $users[array_rand($users)];
		$route              = '/projects/create';
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		/** @var Category $category */
		$category    = $categories[array_rand($categories)];
		$name        = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'project_create' => [
				'_name'        => $name,
				'_description' => $description,
				'_category'    => $category->getId(),
				'_token'       => IH::getCsrf(static::$container)->getToken('_project_create[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertNull(
			IH::getRepository(static::$container, ProjectRepository::class)
			  ->findOneBy(compact('name', 'description', 'category'))
		);
	}
	
	public function testCreateDoesNotWorkForAnon(): void {
		$route              = '/projects/create';
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		/** @var Category $category */
		$category    = $categories[array_rand($categories)];
		$name        = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'project_create' => [
				'_name'        => $name,
				'_description' => $description,
				'_category'    => $category->getId(),
				'_token'       => IH::getCsrf(static::$container)->getToken('_project_create[_csrf_token]'),
			],
		];
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
		static::assertNull(
			IH::getRepository(static::$container, ProjectRepository::class)
			  ->findOneBy(compact('name', 'description', 'category'))
		);
	}
	
	public function testCreateDoesNotWorkForRegularUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository     = IH::getRepository(static::$container, UserRepository::class);
		$users              = $userRepository->findByRole(User::ROLE_USER);
		$creator            = $users[array_rand($users)];
		$route              = '/projects/create';
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		/** @var Category $category */
		$category    = $categories[array_rand($categories)];
		$name        = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'project_create' => [
				'_name'        => $name,
				'_description' => $description,
				'_category'    => $category->getId(),
				'_token'       => IH::getCsrf(static::$container)->getToken('_project_create[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		static::assertNull(
			IH::getRepository(static::$container, ProjectRepository::class)
			  ->findOneBy(compact('name', 'description', 'category'))
		);
	}
}