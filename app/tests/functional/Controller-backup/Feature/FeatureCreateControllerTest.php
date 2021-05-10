<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Feature;

use App\Entity\Feature;
use App\Repository\FeatureRepository;
use App\Tests\BaseWebTestCase;
use App\Tests\functional\Concerns\TokenManagerConcern;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

class FeatureCreateControllerTest extends BaseWebTestCase {
	use TokenManagerConcern;
	
	public function testCreatePage(): void {
		$user    = $this->getOneProjectLead();
		$project = $this->getOneProject();
		$route   = sprintf('/projects/%s/features/create', $project->getUuid());
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testCreate(): void {
		$user        = $this->getOneProjectLead();
		$project     = $this->getOneProject();
		$route       = sprintf('/projects/%s/features/create', $project->getUuid());
		$token       = $this->getTokenManager()->getToken('_feature_create[_csrf_token]');
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraphs(5, true);
		$this->client->loginUser($user);
		$this->client->request(
			Request::METHOD_POST,
			$route,
			[
				'feature_create' => [
					'_title'       => $title,
					'_description' => $description,
					'_token'       => $token,
				],
			]
		);
		/** @var FeatureRepository $featureRepository */
		$featureRepository = $this->getRepository(FeatureRepository::class);
		$feature           = $featureRepository->findOneBy(['title' => $title]);
		static::assertTrue($feature instanceof Feature);
		static::assertTrue($feature?->getDescription() === $description);
	}
	
	public function testCreateForTestUser(): void {
		$user        = $this->getTestUser();
		$project     = $this->getOneProject();
		$route       = sprintf('/projects/%s/features/create', $project->getUuid());
		$token       = $this->getTokenManager()->getToken('_feature_create[_csrf_token]');
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraphs(5, true);
		$this->client->loginUser($user);
		$this->client->request(
			Request::METHOD_POST,
			$route,
			[
				'feature_create' => [
					'_title'       => $title,
					'_description' => $description,
					'_token'       => $token,
				],
			]
		);
		/** @var FeatureRepository $featureRepository */
		$featureRepository = $this->getRepository(FeatureRepository::class);
		$feature           = $featureRepository->findOneBy(['title' => $title]);
		static::assertNull($feature);
	}
}