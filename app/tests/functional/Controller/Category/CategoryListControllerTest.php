<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Category;

use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;

class CategoryListControllerTest extends BaseWebTestCase {
	public function testList(): void {
		$user = $this->getOneProjectLead();
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, '/categories');
		static::assertResponseIsSuccessful();
	}
	
	public function testListForTestUser(): void {
		$user = $this->getTestUser();
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, '/categories');
		static::assertResponseIsSuccessful();
	}
}