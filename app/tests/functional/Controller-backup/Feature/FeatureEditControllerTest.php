<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Feature;

use App\Entity\Feature;
use App\Entity\Project;
use App\Repository\FeatureRepository;
use App\Tests\BaseWebTestCase;
use App\Tests\functional\Concerns\TokenManagerConcern;
use Symfony\Component\HttpFoundation\Request;
use function dd;
use function sprintf;

class FeatureEditControllerTest extends BaseWebTestCase {
	use TokenManagerConcern;
	
	public function testEditPage(): void {
		$user    = $this->getOneProjectLead();
		$project = $this->getOneProjectWithFeatures();
		/** @var Feature $feature */
		$feature = $project->getFeatures()->first();
		$route   = sprintf('/projects/%s/features/%s/edit', $project->getUuid(), $feature->getUuid());
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testEdit(): void {
		$user    = $this->getOneProjectLead();
		$project = $this->getOneProjectWithFeatures();
		/** @var Feature $feature */
		$feature        = $project->getFeatures()->first();
		$route          = sprintf('/projects/%s/features/%s/edit', $project->getUuid(), $feature->getUuid());
		$oldTitle       = $feature->getTitle();
		$oldDescription = $feature->getDescription();
		$token          = $this->getTokenManager()->getToken('_feature_edit[_csrf_token]');
		$title          = $this->getFaker()->sentence;
		$description    = $this->getFaker()->paragraphs(5, true);
		$this->client->loginUser($user);
		$this->client->request(
			Request::METHOD_POST,
			$route,
			[
				'feature_edit' => [
					'_title'       => $title,
					'_description' => $description,
					'_token'       => $token,
				],
			]
		);
		/** @var FeatureRepository $featureRepository */
		$featureRepository = $this->getRepository(FeatureRepository::class);
		$feature           = $featureRepository->find($feature->getId());
		static::assertTrue($feature instanceof Feature);
		static::assertTrue($feature?->getTitle() === $title);
		static::assertTrue($feature?->getDescription() === $description);
		static::assertTrue($feature?->getTitle() !== $oldTitle);
		static::assertTrue($feature?->getDescription() !== $oldDescription);
	}
	
	public function testEditForTestUser(): void {
		$user    = $this->getTestUser();
		$project = $this->getOneProjectWithFeatures();
		/** @var Feature $feature */
		$feature        = $project->getFeatures()->first();
		$route          = sprintf('/projects/%s/features/%s/edit', $project->getUuid(), $feature->getUuid());
		$oldTitle       = $feature->getTitle();
		$oldDescription = $feature->getDescription();
		$token          = $this->getTokenManager()->getToken('_feature_edit[_csrf_token]');
		$title          = $this->getFaker()->sentence;
		$description    = $this->getFaker()->paragraphs(5, true);
		$this->client->loginUser($user);
		$this->client->request(
			Request::METHOD_POST,
			$route,
			[
				'feature_edit' => [
					'_title'       => $title,
					'_description' => $description,
					'_token'       => $token,
				],
			]
		);
		/** @var FeatureRepository $featureRepository */
		$featureRepository = $this->getRepository(FeatureRepository::class);
		$feature           = $featureRepository->find($feature->getId());
		static::assertTrue($feature instanceof Feature);
		static::assertTrue($feature?->getTitle() !== $title);
		static::assertTrue($feature?->getDescription() !== $description);
		static::assertTrue($feature?->getTitle() === $oldTitle);
		static::assertTrue($feature?->getDescription() === $oldDescription);
	}
}