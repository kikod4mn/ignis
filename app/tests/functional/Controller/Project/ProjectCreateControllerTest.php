<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Project;

use App\Entity\Project;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;
use App\Tests\BaseWebTestCase;
use App\Tests\functional\Concerns\TokenManagerConcern;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \App\Controller\Project\CreateController
 */
class ProjectCreateControllerTest extends BaseWebTestCase {
	use TokenManagerConcern;
	
	public function testProjectCreatePage(): void {
		$user = $this->getOneProjectLead();
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, '/projects/create');
		static::assertResponseIsSuccessful();
	}
	
	public function testProjectCreatePageDoesNotWorkForUser(): void {
		$user = $this->getOneActiveUser();
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, '/projects/create');
		static::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
	}
	
	public function testProjectCreate(): void {
		$user = $this->getOneProjectLead();
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = $this->getRepository(CategoryRepository::class);
		$category           = $categoryRepository->findAll()[0];
		$token              = $this->getTokenManager()->getToken('_project_create[_csrf_token]');
		$name               = $this->getFaker()->sentence;
		$description        = $this->getFaker()->paragraphs(5, true);
		$this->client->loginUser($user);
		$this->client->request(
			Request::METHOD_POST,
			'/projects/create',
			[
				'project_create' => [
					'_name'        => $name,
					'_category'    => $category->getId(),
					'_description' => $description,
					'_token'       => $token,
				],
			]
		);
		/** @var ProjectRepository $projectRepository */
		$projectRepository = $this->getRepository(ProjectRepository::class);
		$project           = $projectRepository->findOneBy(['name' => $name]);
		static::assertTrue($project instanceof Project);
		static::assertTrue($project?->getCategory()?->getId() === $category->getId());
		static::assertTrue($project?->getDescription() === $description);
	}
	
	public function testProjectCreateForTestUser(): void {
		$user = $this->getTestUser();
		/** @var CategoryRepository $categoryRepository */
		$categoryRepository = $this->getRepository(CategoryRepository::class);
		$category           = $categoryRepository->findAll()[0];
		$token              = $this->getTokenManager()->getToken('_project_create[_csrf_token]');
		$name               = $this->getFaker()->sentence;
		$description        = $this->getFaker()->paragraphs(5, true);
		$this->client->loginUser($user);
		$this->client->request(
			Request::METHOD_POST,
			'/projects/create',
			[
				'project_create' => [
					'_name'        => $name,
					'_category'    => $category->getId(),
					'_description' => $description,
					'_token'       => $token,
				],
			]
		);
		/** @var ProjectRepository $projectRepository */
		$projectRepository = $this->getRepository(ProjectRepository::class);
		$project           = $projectRepository->findOneBy(['name' => $name]);
		static::assertNull($project);
	}
}