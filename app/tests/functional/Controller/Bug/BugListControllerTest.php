<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Bug;

use App\Entity\User;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

class BugListControllerTest extends BaseWebTestCase {
	public function testBugList(): void {
		$project = $this->getOneProject();
		/** @var User $user */
		$user = $project->getAuthor();
		$this->client->loginUser($user);
		$route = sprintf('/projects/%s/bugs', $project->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testBugForTestUser(): void {
		$project = $this->getOneProject();
		$this->client->loginUser($this->getTestUser());
		$route = sprintf('/projects/%s/bugs', $project->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
}