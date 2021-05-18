<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Category;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Tests\BaseWebTestCase;
use App\Tests\functional\Concerns\TokenManagerConcern;
use Symfony\Component\HttpFoundation\Request;

class CategoryCreateControllerTest extends BaseWebTestCase {
	use TokenManagerConcern;
	
	public function testCreatePage(): void {
		$user = $this->getOneAdmin();
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, '/categories/create');
		static::assertResponseIsSuccessful();
	}
	
	public function testCreate(): void {
		$user = $this->getOneAdmin();
		$this->client->loginUser($user);
		$name  = $this->getFaker()->words(2, true);
		$token = $this->getTokenManager()->getToken('_category_create[_csrf_token]');
		$this->client->request(
			Request::METHOD_POST,
			'/categories/create',
			[
				'category_create' => [
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
	
	public function testCreateForTestUser(): void {
		$this->client->loginUser($this->getTestUser());
		$name  = $this->getFaker()->words(2, true);
		$token = $this->getTokenManager()->getToken('_category_create[_csrf_token]');
		$this->client->request(
			Request::METHOD_POST,
			'/categories/create',
			[
				'category_create' => [
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