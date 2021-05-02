<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Account;

use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use function htmlentities;
use function sprintf;

class PublicProfileControllerTest extends BaseWebTestCase {
	public function testRegularUserCanSeeOtherUsers(): void {
		$user       = $this->getOneActiveUser();
		$userToView = $this->getOneProjectLead();
		$this->client->loginUser($user);
		$route = sprintf('/%s/profile', $userToView->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		$responseText = (string) $this->client->getResponse()->getContent();
		static::assertStringContainsStringIgnoringCase('PROFILE', $responseText);
		static::assertStringContainsStringIgnoringCase('ACTIVITY', $responseText);
		static::assertStringContainsStringIgnoringCase(htmlentities($userToView->getName()), $responseText);
	}
	
	public function testTheTestUserCanNotSeeOtherUsers(): void {
		$user       = $this->getTestUser();
		$userToView = $this->getOneActiveUser();
		$this->client->loginUser($user);
		$route = sprintf('/%s/profile', $userToView->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		$responseText = (string) $this->client->getResponse()->getContent();
		static::assertStringContainsStringIgnoringCase('PROFILE', $responseText);
		static::assertStringContainsStringIgnoringCase('ACTIVITY', $responseText);
		static::assertStringContainsStringIgnoringCase(htmlentities($user->getName()), $responseText);
	}
}