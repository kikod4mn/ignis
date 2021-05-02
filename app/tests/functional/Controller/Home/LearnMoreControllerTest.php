<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Home;

use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;

class LearnMoreControllerTest extends BaseWebTestCase {
	public function testLearnMore():void {
		$this->client->request(Request::METHOD_GET, '/learn-more');
		static::assertResponseIsSuccessful();
	}
}