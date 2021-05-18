<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Project;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

/**
 * @covers \App\Controller\Project\DeleteController
 */
class ProjectDeleteControllerTest extends BaseWebTestCase {
	public function testDelete(): void {
		$project   = $this->getOneProject();
		$projectId = $project->getId();
		/** @var User $user */
		$user = $project->getAuthor();
		$this->client->loginUser($user);
		$route = sprintf('/projects/%s/delete', $project->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		$this->client->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var ProjectRepository $projectRepository */
		$projectRepository = $this->getRepository(ProjectRepository::class);
		$project           = $projectRepository->find($projectId);
		static::assertNull($project);
	}
	
	public function testDeleteForTestUser(): void {
		$project   = $this->getOneProject();
		$projectId = $project->getId();
		$user      = $this->getTestUser();
		$this->client->loginUser($user);
		$route = sprintf('/projects/%s/delete', $project->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		$this->client->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var ProjectRepository $projectRepository */
		$projectRepository = $this->getRepository(ProjectRepository::class);
		$project           = $projectRepository->find($projectId);
		static::assertTrue($project instanceof Project);
	}
}