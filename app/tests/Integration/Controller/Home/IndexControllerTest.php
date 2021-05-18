<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Home;

use App\Tests\Integration\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;

class IndexControllerTest extends BaseWebTestCase {
	public function testShowIndex(): void {
		$this->getClient()->request(Request::METHOD_GET, '/');
		static::assertResponseIsSuccessful();
	}
}
