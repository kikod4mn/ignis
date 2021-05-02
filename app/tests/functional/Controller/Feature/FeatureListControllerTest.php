<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Feature;

use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

/**
 * @covers \App\Controller\Feature\ListController
 */
class FeatureListControllerTest extends BaseWebTestCase {
	public function testList(): void {
		$project = $this->getOneProjectWithFeatures();
		$user    = $this->getOneProjectLead();
		$route   = sprintf('/projects/%s/features', $project->getUuid());
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testListForTestUser(): void {
		$project = $this->getOneProjectWithFeatures();
		$user    = $this->getTestUser();
		$route   = sprintf('/projects/%s/features', $project->getUuid());
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
}