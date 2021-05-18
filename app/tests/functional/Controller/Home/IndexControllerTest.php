<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Home;

use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;

class IndexControllerTest extends BaseWebTestCase {
	public function testShowIndex(): void {
		$this->client->request(Request::METHOD_GET, '/');
		static::assertResponseIsSuccessful();
	}
}
