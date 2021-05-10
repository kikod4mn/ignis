<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Project;

use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \App\Controller\Project\ListController
 */
class ProjectListControllerTest extends BaseWebTestCase {
	public function testProjectList(): void {
		$user = $this->getOneProjectLead();
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, '/projects');
		static::assertResponseIsSuccessful();
	}
	
	public function testProjectListDoesNotWorkForAnonymous(): void {
		$this->client->request(Request::METHOD_GET, '/projects');
		static::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
	}
	
	public function testProjectListForTestUser(): void {
		$user = $this->getTestUser();
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, '/projects');
		static::assertResponseIsSuccessful();
	}
}