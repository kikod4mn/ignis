<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Feature;

use App\Entity\Feature;
use App\Repository\FeatureRepository;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

class FeatureDeleteControllerTest extends BaseWebTestCase {
	public function testDelete(): void {
		$user    = $this->getOneProjectLead();
		$project = $this->getOneProjectWithFeatures();
		$feature = $project->getFeatures()[0];
		$route   = sprintf('/projects/%s/features/%s/delete', $project->getUuid(), $feature?->getUuid());
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, $route);
		/** @var FeatureRepository $featureRepository */
		$featureRepository = $this->getRepository(FeatureRepository::class);
		$feature           = $featureRepository->findOneBy(['uuid' => $feature?->getUuid()]);
		static::assertNull($feature);
	}
	
	public function testDeleteForTestUser(): void {
		$user    = $this->getTestUser();
		$project = $this->getOneProjectWithFeatures();
		$feature = $project->getFeatures()[0];
		$route   = sprintf('/projects/%s/features/%s/delete', $project->getUuid(), $feature?->getUuid());
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, $route);
		/** @var FeatureRepository $featureRepository */
		$featureRepository = $this->getRepository(FeatureRepository::class);
		$feature           = $featureRepository->findOneBy(['uuid' => $feature?->getUuid()]);
		static::assertTrue($feature instanceof Feature);
	}
}