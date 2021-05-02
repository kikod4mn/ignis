<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Project;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Tests\BaseWebTestCase;
use App\Tests\functional\Concerns\TokenManagerConcern;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

/**
 * @covers \App\Controller\Project\EditController
 */
class ProjectEditControllerTest extends BaseWebTestCase {
	use TokenManagerConcern;
	
	public function testProjectEditPage(): void {
		$project = $this->getOneProject();
		/** @var User $user */
		$user  = $project->getAuthor();
		$route = sprintf('/projects/%s/edit', $project->getUuid());
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testProjectEditWorks(): void {
		$project = $this->getOneProject();
		/** @var User $user */
		$user           = $project->getAuthor();
		$route          = sprintf('/projects/%s/edit', $project->getUuid());
		$token          = $this->getTokenManager()->getToken('_project_edit[_csrf_token]');
		$name           = $this->getFaker()->sentence . 'random';
		$oldName        = $project->getName();
		$description    = $this->getFaker()->paragraphs(3, true);
		$oldDescription = $project->getDescription();
		$category       = $this->getOneCategoryNot($project);
		$oldCategoryId  = $project->getCategory()?->getId();
		$this->client->loginUser($user);
		$this->client->request(
			Request::METHOD_POST,
			$route,
			[
				'project_edit' => [
					'_token'       => $token,
					'_name'        => $name,
					'_description' => $description,
					'_category'    => $category->getId(),
				],
			]
		);
		$projectRepository = $this->getRepository(ProjectRepository::class);
		/** @var Project $project */
		$project = $projectRepository->find($project->getId());
		static::assertTrue($name === $project->getName());
		static::assertTrue($oldName !== $project->getName());
		static::assertTrue($description === $project->getDescription());
		static::assertTrue($oldDescription !== $project->getDescription());
		static::assertTrue($category->getId() === $project->getCategory()?->getId());
		static::assertTrue($oldCategoryId !== $project->getCategory()?->getId());
		static::assertNotNull($project->getUpdatedAt());
	}
	
	public function testProjectEditWorksForTestUser(): void {
		$project        = $this->getOneProject();
		$user           = $this->getTestUser();
		$route          = sprintf('/projects/%s/edit', $project->getUuid());
		$token          = $this->getTokenManager()->getToken('_project_edit[_csrf_token]');
		$name           = $this->getFaker()->sentence . 'random';
		$oldName        = $project->getName();
		$description    = $this->getFaker()->paragraphs(3, true);
		$oldDescription = $project->getDescription();
		$category       = $this->getOneCategoryNot($project);
		$oldCategoryId  = $project->getCategory()?->getId();
		$this->client->loginUser($user);
		$this->client->request(
			Request::METHOD_POST,
			$route,
			[
				'project_edit' => [
					'_token'       => $token,
					'_name'        => $name,
					'_description' => $description,
					'_category'    => $category->getId(),
				],
			]
		);
		$projectRepository = $this->getRepository(ProjectRepository::class);
		/** @var Project $project */
		$project = $projectRepository->find($project->getId());
		static::assertTrue($name !== $project->getName());
		static::assertTrue($oldName === $project->getName());
		static::assertTrue($description !== $project->getDescription());
		static::assertTrue($oldDescription === $project->getDescription());
		static::assertTrue($category->getId() !== $project->getCategory()?->getId());
		static::assertTrue($oldCategoryId === $project->getCategory()?->getId());
		static::assertNotNull($project->getUpdatedAt());
	}
}