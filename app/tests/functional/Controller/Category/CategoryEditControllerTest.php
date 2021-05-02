<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Category;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Tests\BaseWebTestCase;
use App\Tests\functional\Concerns\TokenManagerConcern;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

class CategoryEditControllerTest extends BaseWebTestCase {
	use TokenManagerConcern;
	
	public function testEditPage(): void {
		$user     = $this->getOneAdmin();
		$category = $this->getOneProject()->getCategory();
		$route    = sprintf('/categories/%s/edit', $category?->getUuid());
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testEdit(): void {
		$user     = $this->getOneAdmin();
		$category = $this->getOneProject()->getCategory();
		$name     = $this->getFaker()->words(2, true);
		$token    = $this->getTokenManager()->getToken('_category_edit[_csrf_token]');
		$route    = sprintf('/categories/%s/edit', $category?->getUuid());
		$this->client->loginUser($user);
		$this->client->request(
			Request::METHOD_POST,
			$route,
			[
				'category_edit' => [
					'_name'  => $name,
					'_token' => $token,
				],
			]
		);
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = $this->getRepository(CategoryRepository::class);
		$category           = $categoryRepository->findOneBy(['name' => $name]);
		static::assertTrue($category instanceof Category);
	}
	
	public function testEditForTestUser(): void {
		$user     = $this->getTestUser();
		$category = $this->getOneProject()->getCategory();
		$name     = $this->getFaker()->words(2, true);
		$token    = $this->getTokenManager()->getToken('_category_edit[_csrf_token]');
		$route    = sprintf('/categories/%s/edit', $category?->getUuid());
		$this->client->loginUser($user);
		$this->client->request(
			Request::METHOD_POST,
			$route,
			[
				'category_edit' => [
					'_name'  => $name,
					'_token' => $token,
				],
			]
		);
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = $this->getRepository(CategoryRepository::class);
		$category           = $categoryRepository->findOneBy(['name' => $name]);
		static::assertNull($category);
	}
}