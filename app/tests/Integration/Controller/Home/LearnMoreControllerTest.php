<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Home;

use App\Tests\Integration\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;

class LearnMoreControllerTest extends BaseWebTestCase {
	public function testLearnMore():void {
		$this->getClient()->request(Request::METHOD_GET, '/learn-more');
		static::assertResponseIsSuccessful();
	}
}