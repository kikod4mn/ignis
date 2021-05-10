<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\User\Account;

use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IntegrationHelp;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\User\Profile\PrivateProfileController
 */
class PrivateProfileControllerTest extends BaseWebTestCase {
	public function testPrivateProfile(): void {
		$user = IntegrationHelp::getActiveUser(static::$container);
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, '/profile');
		static::assertResponseIsSuccessful();
		$responseText = (string) $this->client->getResponse()->getContent();
		static::assertStringContainsStringIgnoringCase('PROFILE', $responseText);
		static::assertStringContainsStringIgnoringCase('ACTIVITY', $responseText);
		static::assertStringContainsStringIgnoringCase(htmlentities((string) $user->getName()), $responseText);
	}
	
	public function testPrivateProfileDoesntWorkForAnon(): void {
		$this->client->request(Request::METHOD_GET, '/profile');
		static::assertResponseStatusCodeSame(302);
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->client->getResponse()->getContent());
	}
	
	public function testAccountPageExampleWorksForTestUser(): void {
		$user = IntegrationHelp::getTestUser(static::$container);
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, '/profile');
		static::assertResponseIsSuccessful();
		$responseText = (string) $this->client->getResponse()->getContent();
		static::assertStringContainsStringIgnoringCase('PROFILE', $responseText);
		static::assertStringContainsStringIgnoringCase('ACTIVITY', $responseText);
		static::assertStringContainsStringIgnoringCase(htmlentities((string) $user->getName()), $responseText);
	}
}