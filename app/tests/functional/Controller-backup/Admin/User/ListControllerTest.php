<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Admin\User;

use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ListControllerTest extends BaseWebTestCase {
	public function testList(): void {
		$admin = $this->getOneAdmin();
		$this->client->loginUser($admin);
		$this->client->request(Request::METHOD_GET, '/admin/users');
		static::assertResponseIsSuccessful();
	}
}