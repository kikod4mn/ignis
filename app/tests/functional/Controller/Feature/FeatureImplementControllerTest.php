<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Feature;

use App\Repository\FeatureRepository;
use App\Tests\BaseWebTestCase;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

class FeatureImplementControllerTest extends BaseWebTestCase {
	public function testImplement(): void {
		/** @var FeatureRepository $featureRepository */
		$featureRepository = $this->getRepository(FeatureRepository::class);
		$feature           = $featureRepository->findBy(['implemented' => false])[0];
		$project           = $feature->getProject();
		$route             = sprintf('/projects/%s/features/%s/implement', $project?->getUuid(), $feature->getUuid());
		$this->client->loginUser($this->getOneProjectLead());
		$this->client->request(Request::METHOD_GET, $route);
		$feature = $featureRepository->findOneBy(['id' => $feature->getId()]);
		static::assertTrue($feature?->isImplemented());
		static::assertTrue($feature?->getImplementedAt() instanceof DateTimeInterface);
	}
	
	public function testImplementForTestUser(): void {
		/** @var FeatureRepository $featureRepository */
		$featureRepository = $this->getRepository(FeatureRepository::class);
		$feature           = $featureRepository->findBy(['implemented' => false])[0];
		$project           = $feature->getProject();
		$route             = sprintf('/projects/%s/features/%s/implement', $project?->getUuid(), $feature->getUuid());
		$this->client->loginUser($this->getTestUser());
		$this->client->request(Request::METHOD_GET, $route);
		$feature = $featureRepository->findOneBy(['id' => $feature->getId()]);
		static::assertFalse($feature?->isImplemented());
		static::assertFalse($feature?->getImplementedAt() instanceof DateTimeInterface);
	}
}