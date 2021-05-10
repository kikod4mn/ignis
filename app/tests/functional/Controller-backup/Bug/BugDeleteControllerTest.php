<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Bug;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

class BugDeleteControllerTest extends BaseWebTestCase {
	public function testBugDelete(): void {
		$bug = $this->getOneBug();
		/** @var Project $project */
		$project = $bug->getProject();
		/** @var User $projectLead */
		$projectLead = $project->getAuthor();
		$route       = sprintf(
			'/projects/%s/bugs/%s/delete',
			$project->getUuid(), $bug->getUuid()
		);
		static::assertTrue($project->getBugs()->contains($bug));
		$this->client->loginUser($projectLead);
		$this->client->request(Request::METHOD_GET, $route);
		$project = $this->getRepository(ProjectRepository::class)->findOneBy(['uuid' => $project->getUuid()]);
		static::assertFalse($project->getBugs()->contains($bug));
	}
	
	public function testBugDeleteForTestUser(): void {
		$bug = $this->getOneBug();
		$project = $bug->getProject();
		$user = $this->getTestUser();
		$route       = sprintf(
			'/projects/%s/bugs/%s/delete',
			$project->getUuid(), $bug->getUuid()
		);
		static::assertTrue($project->getBugs()->contains($bug));
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, $route);
		$project = $this->getRepository(ProjectRepository::class)->findOneBy(['uuid' => $project->getUuid()]);
		static::assertTrue($project->getBugs()->contains($bug));
	}
}