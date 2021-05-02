<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Category;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

/**
 * @covers \App\Controller\Category\ShowController
 */
class CategoryShowControllerTest extends BaseWebTestCase {
	public function testShow(): void {
		/** @var CategoryRepository $categoryRepo */
		$categoryRepo = $this->getRepository(CategoryRepository::class);
		$category     = $categoryRepo->findAll()[0];
		$user         = $this->getOneProjectLead();
		static::assertTrue($category instanceof Category);
		$this->client->loginUser($user);
		$route = sprintf('/categories/%s/show', $category->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testShowForTestUser(): void {
		/** @var CategoryRepository $categoryRepo */
		$categoryRepo = $this->getRepository(CategoryRepository::class);
		$category     = $categoryRepo->findAll()[0];
		$user         = $this->getTestUser();
		static::assertTrue($category instanceof Category);
		$this->client->loginUser($user);
		$route = sprintf('/categories/%s/show', $category->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
}