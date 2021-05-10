<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Home;

use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;

class TermsAndConditionsControllerTest extends BaseWebTestCase {
	public function testTerms():void {
		$this->client->request(Request::METHOD_GET, '/terms-and-conditions');
		static::assertResponseIsSuccessful();
	}
}