<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Home;

use App\Tests\Integration\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;

class TermsAndConditionsControllerTest extends BaseWebTestCase {
	public function testTerms(): void {
		$this->getClient()->request(Request::METHOD_GET, '/terms-and-conditions');
		static::assertResponseIsSuccessful();
	}
}