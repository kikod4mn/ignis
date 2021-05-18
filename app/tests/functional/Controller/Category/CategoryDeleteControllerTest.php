<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Category;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use function count;
use function dd;
use function sprintf;

class CategoryDeleteControllerTest extends BaseWebTestCase {
	public function testDelete(): void {
		$user = $this->getOneAdmin();
		/** @var Category $category */
		$category      = $this->getOneProject()->getCategory();
		$route         = sprintf('/categories/%s/delete', $category->getUuid());
		$projects      = $category->getProjects();
		$projectsCount = $projects->count();
		$projectIds    = [];
		foreach ($projects as $project) {
			$projectIds[] = $project->getUuid();
		}
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, $route);
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = $this->getRepository(CategoryRepository::class);
		$category           = $categoryRepository->findOneBy(['uuid' => $category->getUuid()]);
		/** @var ProjectRepository $projectRepository */
		$projectRepository = $this->getRepository(ProjectRepository::class);
		$projects          = $projectRepository->findBy(['uuid' => $projectIds]);
		static::assertNull($category);
		static::assertTrue($projectsCount === count($projects));
	}
	
	public function testDeleteForTestUser(): void {
		$user = $this->getTestUser();
		/** @var Category $category */
		$category = $this->getOneProject()->getCategory();
		$route    = sprintf('/categories/%s/delete', $category->getUuid());
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, $route);
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = $this->getRepository(CategoryRepository::class);
		$category           = $categoryRepository->findOneBy(['uuid' => $category->getUuid()]);
		static::assertTrue($category instanceof Category);
	}
}