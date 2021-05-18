<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Account;

use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use function htmlentities;

class PrivateProfileControllerTest extends BaseWebTestCase {
	public function testPrivateProfileShowsForSelf(): void {
		$user = $this->getOneActiveUser();
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, '/profile');
		static::assertResponseIsSuccessful();
		$responseText = (string) $this->client->getResponse()->getContent();
		static::assertStringContainsStringIgnoringCase('PROFILE', $responseText);
		static::assertStringContainsStringIgnoringCase('ACTIVITY', $responseText);
		static::assertStringContainsStringIgnoringCase(htmlentities((string) $user->getName()), $responseText);
	}
	
	public function testAccountPageExampleWorksForTestUser(): void {
		$user = $this->getTestUser();
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, '/profile');
		static::assertResponseIsSuccessful();
		$responseText = (string) $this->client->getResponse()->getContent();
		static::assertStringContainsStringIgnoringCase('PROFILE', $responseText);
		static::assertStringContainsStringIgnoringCase('ACTIVITY', $responseText);
		static::assertStringContainsStringIgnoringCase(htmlentities((string) $user->getName()), $responseText);
	}
}