<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\User\Account;

use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IntegrationHelp;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\User\Profile\PublicProfileController
 */
class PublicProfileControllerTest extends BaseWebTestCase {
	public function testRegularUserCanSeeOtherUsers(): void {
		$user       = IntegrationHelp::getActiveUser(static::$container);
		$userToView = IntegrationHelp::getProjectLeader(static::$container);
		$this->client->loginUser($user);
		$route = sprintf('/%s/profile', $userToView->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
		$responseText = (string) $this->client->getResponse()->getContent();
		static::assertStringContainsStringIgnoringCase('PROFILE', $responseText);
		static::assertStringContainsStringIgnoringCase('ACTIVITY', $responseText);
		static::assertStringContainsStringIgnoringCase(htmlentities((string) $userToView->getName()), $responseText);
	}
	
	public function testPublicProfileDoesntWorkForAnon(): void {
		$userToView = IntegrationHelp::getProjectLeader(static::$container);
		$route      = sprintf('/%s/profile', $userToView->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->client->getResponse()->getContent());
	}
	
	public function testTheTestUserCanNotSeeOtherUsers(): void {
		$user       = IntegrationHelp::getTestUser(static::$container);
		$userToView = IntegrationHelp::getProjectLeader(static::$container);
		$this->client->loginUser($user);
		$route = sprintf('/%s/profile', $userToView->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
		$responseText = (string) $this->client->getResponse()->getContent();
		static::assertStringContainsStringIgnoringCase('PROFILE', $responseText);
		static::assertStringContainsStringIgnoringCase('ACTIVITY', $responseText);
		static::assertStringContainsStringIgnoringCase(htmlentities((string) $user->getName()), $responseText);
	}
}