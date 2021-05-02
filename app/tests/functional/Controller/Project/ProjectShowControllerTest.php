<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Project;

use App\Entity\User;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function sprintf;

/**
 * @covers \App\Controller\Project\ShowController
 */
class ProjectShowControllerTest extends BaseWebTestCase {
	public function testProjectShow(): void {
		$project = $this->getOneProject();
		/** @var User $user */
		$user = $project->getAuthor();
		$this->client->loginUser($user);
		$route = sprintf('projects/%s/show', (string) $project->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testProjectShowDoesNotWorkForAnonymous(): void {
		$project = $this->getOneProject();
		$route   = sprintf('projects/%s/show', (string) $project->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
	}
	
	public function testProjectShowForTestUser(): void {
		$project = $this->getOneProject();
		$user    = $this->getTestUser();
		$this->client->loginUser($user);
		$route = sprintf('projects/%s/show', (string) $project->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
}