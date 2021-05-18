<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Project;

use App\Entity\Category;
use App\Entity\Project;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Project\EditController
 */
class EditControllerTest extends BaseWebTestCase {
	public function testEditPage(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u): bool => $u->getProjects()->count() > 5
		);
		$creator        = $users[array_rand($users)];
		/** @var Project $project */
		$project = $creator->getProjects()[0];
		$route   = sprintf('/projects/%s/edit', $project->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testEditPageForAnon(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u): bool => $u->getProjects()->count() > 5
		);
		$creator        = $users[array_rand($users)];
		/** @var Project $project */
		$project = $creator->getProjects()[0];
		$route   = sprintf('/projects/%s/edit', $project->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testEditPageForRegularUser(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u): bool => $u->getProjects()->count() > 5
		);
		$creator        = $users[array_rand($users)];
		/** @var Project $project */
		$project = $creator->getProjects()[0];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = sprintf('/projects/%s/edit', $project->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
	}
	
	public function testEdit(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u): bool => $u->getProjects()->count() > 5
		);
		$creator        = $users[array_rand($users)];
		/** @var Project $project */
		$project            = $creator->getProjects()[0];
		$route              = sprintf('/projects/%s/edit', $project->getUuid()?->toString());
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = array_filter(
			$categoryRepository->findAll(),
			static fn (Category $c): bool => $c->getId() !== $project->getCategory()?->getId()
		);
		/** @var Category $category */
		$category    = $categories[array_rand($categories)];
		$name        = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'project_edit' => [
				'_name'        => $name,
				'_description' => $description,
				'_category'    => $category->getId(),
				'_token'       => IH::getCsrf(static::$container)->getToken('_project_edit[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertNotNull(
			IH::getRepository(static::$container, ProjectRepository::class)
			  ->findOneBy(['name' => $name, 'description' => $description, 'category' => $category])
		);
	}
	
	public function testEditForEditor(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u): bool => $u->getProjects()->count() > 5
		);
		$creator        = $users[array_rand($users)];
		$projects       = array_filter(
			$creator->getProjects()->toArray(),
			static fn (Project $p): bool => $p->getCanEdit()->count() > 0
		);
		/** @var Project $project */
		$project            = $projects[array_rand($projects)];
		$route              = sprintf('/projects/%s/edit', $project->getUuid()?->toString());
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = array_filter(
			$categoryRepository->findAll(),
			static fn (Category $c): bool => $c->getId() !== $project->getCategory()?->getId()
		);
		/** @var Category $category */
		$category    = $categories[array_rand($categories)];
		$name        = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'project_edit' => [
				'_name'        => $name,
				'_description' => $description,
				'_category'    => $category->getId(),
				'_token'       => IH::getCsrf(static::$container)->getToken('_project_edit[_csrf_token]'),
			],
		];
		/** @var User $editor */
		$editor = $project->getCanEdit()[0];
		$this->getClient()->loginUser($editor);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertNotNull(
			IH::getRepository(static::$container, ProjectRepository::class)
			  ->findOneBy(['name' => $name, 'description' => $description, 'category' => $category])
		);
	}
	
	public function testEditForNotEditor(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u): bool => $u->getProjects()->count() > 5
		);
		$creator        = $users[array_rand($users)];
		$projects       = $creator->getProjects()->toArray();
		/** @var Project $project */
		$project            = $projects[array_rand($projects)];
		$route              = sprintf('/projects/%s/edit', $project->getUuid()?->toString());
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = array_filter(
			$categoryRepository->findAll(),
			static fn (Category $c): bool => $c->getId() !== $project->getCategory()?->getId()
		);
		/** @var Category $category */
		$category    = $categories[array_rand($categories)];
		$name        = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'project_edit' => [
				'_name'        => $name,
				'_description' => $description,
				'_category'    => $category->getId(),
				'_token'       => IH::getCsrf(static::$container)->getToken('_project_edit[_csrf_token]'),
			],
		];
		$notEditors  = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u): bool => $u->getId() !== $project->getAuthor()?->getId()
										 && ! $project->getCanEdit()->contains($u)
										 && ! $project->getCanView()->contains($u)
		);
		/** @var User $notEditor */
		$notEditor = $notEditors[array_rand($notEditors)];
		$this->getClient()->loginUser($notEditor);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		static::assertNull(
			IH::getRepository(static::$container, ProjectRepository::class)
			  ->findOneBy(['name' => $name, 'description' => $description, 'category' => $category])
		);
	}
	
	public function testEditForTestUser(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_TEST_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $creator */
		$creator = $userRepository->findOneByRoles([$role]);
		/** @var ProjectRepository $projectRepository */
		$projectRepository = IH::getRepository(static::$container, ProjectRepository::class);
		/** @var Project $project */
		$project            = $projectRepository->findOneBy(['softDeleted' => false]);
		$route              = sprintf('/projects/%s/edit', $project->getUuid()?->toString());
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		/** @var Category $category */
		$category    = $categories[array_rand($categories)];
		$name        = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'project_edit' => [
				'_name'        => $name,
				'_description' => $description,
				'_category'    => $category->getId(),
				'_token'       => IH::getCsrf(static::$container)->getToken('_project_edit[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseIsSuccessful();
		static::assertNull(
			IH::getRepository(static::$container, ProjectRepository::class)
			  ->findOneBy(['name' => $name, 'description' => $description, 'category' => $category])
		);
	}
	
	public function testEditDoesNotWorkForAnon(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u): bool => $u->getProjects()->count() > 5
		);
		$creator        = $users[array_rand($users)];
		$projects       = $creator->getProjects()->toArray();
		/** @var Project $project */
		$project            = $projects[array_rand($projects)];
		$route              = sprintf('/projects/%s/edit', $project->getUuid()?->toString());
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		/** @var Category $category */
		$category    = $categories[array_rand($categories)];
		$name        = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'project_edit' => [
				'_name'        => $name,
				'_description' => $description,
				'_category'    => $category->getId(),
				'_token'       => IH::getCsrf(static::$container)->getToken('_project_edit[_csrf_token]'),
			],
		];
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
		static::assertNull(
			IH::getRepository(static::$container, ProjectRepository::class)
			  ->findOneBy(['name' => $name, 'description' => $description, 'category' => $category])
		);
	}
	
	public function testEditDoesNotWorkForRegularUser(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u): bool => $u->getProjects()->count() > 5
		);
		$creator        = $users[array_rand($users)];
		$projects       = $creator->getProjects()->toArray();
		/** @var Project $project */
		$project = $projects[array_rand($projects)];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_USER]);
		/** @var UserRepository $userRepository */
		$userRepository     = IH::getRepository(static::$container, UserRepository::class);
		$users              = $userRepository->findByRoles([$role]);
		$creator            = $users[array_rand($users)];
		$route              = sprintf('/projects/%s/edit', $project->getUuid()?->toString());
		$categoryRepository = IH::getRepository(static::$container, CategoryRepository::class);
		$categories         = $categoryRepository->findAll();
		/** @var Category $category */
		$category    = $categories[array_rand($categories)];
		$name        = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'project_edit' => [
				'_name'        => $name,
				'_description' => $description,
				'_category'    => $category->getId(),
				'_token'       => IH::getCsrf(static::$container)->getToken('_project_edit[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		static::assertNull(
			IH::getRepository(static::$container, ProjectRepository::class)
			  ->findOneBy(['name' => $name, 'description' => $description, 'category' => $category])
		);
	}
}