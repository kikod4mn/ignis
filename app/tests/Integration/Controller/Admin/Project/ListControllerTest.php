<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Admin\Project;

use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IntegrationHelp;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \App\Controller\Admin\Project\ListController
 */
class ListControllerTest extends BaseWebTestCase {
	public function testList(): void {
		$this->client->loginUser(IntegrationHelp::getAdministrator(static::$container));
		$this->client->request(Request::METHOD_GET, '/admin/projects');
		static::assertResponseIsSuccessful();
	}
	
	public function testListWorksForTestUser(): void {
		$this->client->loginUser(IntegrationHelp::getTestUser(static::$container));
		$this->client->request(Request::METHOD_GET, '/admin/projects');
		static::assertResponseIsSuccessful();
	}
	
	public function testListDoesNotWorkForRegularUser(): void {
		$this->client->loginUser(IntegrationHelp::getActiveUser(static::$container));
		$this->client->request(Request::METHOD_GET, '/admin/projects');
		static::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
	}
	
	public function testListDoesNotWorkForProjectLead(): void {
		$this->client->loginUser(IntegrationHelp::getProjectLeader(static::$container));
		$this->client->request(Request::METHOD_GET, '/admin/projects');
		static::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
	}
	
	public function testListDoesNotWorkForAnon(): void {
		$this->client->request(Request::METHOD_GET, '/admin/projects');
		static::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
	}
}