<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Language;

use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;

class LanguageListControllerTest extends BaseWebTestCase {
	public function testLanguageList(): void {
		$user = $this->getOneProjectLead();
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, '/languages');
		static::assertResponseIsSuccessful();
	}
	
	public function testLanguageListForTestUser(): void {
		$user = $this->getTestUser();
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, '/languages');
		static::assertResponseIsSuccessful();
	}
}