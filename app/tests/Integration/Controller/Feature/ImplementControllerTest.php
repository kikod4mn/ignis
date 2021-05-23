<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Feature;

use App\Entity\Feature;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\FeatureRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Feature\ImplementController
 */
class ImplementControllerTest extends BaseWebTestCase {
	public function testFeatureImplement(): void {
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		$features          = array_filter(
			$featureRepository->findAll(),
			static fn (Feature $f) => ! $f->isImplemented() && ! $f->getSoftDeleted()
		);
		/** @var Feature $feature */
		$feature = $features[array_rand($features)];
		/** @var Project $project */
		$project = $feature->getProject();
		/** @var User $implementer */
		$implementer = $project->getAuthor();
		$route       = sprintf('/projects/%s/features/%s/implement', $project->getUuid()?->toString(), $feature->getUuid()?->toString());
		$this->getClient()->loginUser($implementer);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		static::assertTrue($featureRepository->find($feature->getId())?->isImplemented());
	}
	
	public function testFeatureImplementDoesNotWorkForProjectLeadWhoIsNotConnectedToProject(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $implementer */
		$implementer = $userRepository->findOneByRole(User::ROLE_PROJECT_LEAD);
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		$features          = array_filter(
			$featureRepository->findAll(),
			static fn (Feature $f) => ! $f->isImplemented()
									  && ! $f->getSoftDeleted()
									  && $implementer->getId() !== $f->getAuthor()?->getId()
									  && $implementer->getId() !== $f->getProject()?->getAuthor()?->getId()
									  && ! $f->getProject()?->getCanView()->contains($implementer)
									  && ! $f->getProject()?->getCanEdit()->contains($implementer)
		);
		/** @var Feature $feature */
		$feature = $features[array_rand($features)];
		/** @var Project $project */
		$project = $feature->getProject();
		$route   = sprintf('/projects/%s/features/%s/implement', $project->getUuid()?->toString(), $feature->getUuid()?->toString());
		$this->getClient()->loginUser($implementer);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		static::assertFalse($featureRepository->find($feature->getId())?->isImplemented());
	}
	
	public function testFeatureImplementDoesWorkForFeatureAuthor(): void {
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		$features          = array_filter(
			$featureRepository->findAll(),
			static fn (Feature $f) => ! $f->isImplemented()
									  && ! $f->getSoftDeleted()
									  && $f->getProject()?->getCanEdit()->contains($f->getAuthor())
									  && $f->getProject()?->getCanView()->contains($f->getAuthor())
		);
		/** @var Feature $feature */
		$feature = $features[array_rand($features)];
		/** @var Project $project */
		$project = $feature->getProject();
		/** @var User $implementer */
		$implementer = $feature->getAuthor();
		$route       = sprintf('/projects/%s/features/%s/implement', $project->getUuid()?->toString(), $feature->getUuid()?->toString());
		$this->getClient()->loginUser($implementer);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		static::assertTrue($featureRepository->find($feature->getId())?->isImplemented());
	}
	
	public function testFeatureImplementDoesNotWorkForProjectLeadWhoCannotSeeOrEditProject(): void {
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		$features          = array_filter(
			$featureRepository->findAll(),
			static fn (Feature $f) => ! $f->isImplemented()
									  && ! $f->getSoftDeleted()
		);
		/** @var Feature $feature */
		$feature = $features[array_rand($features)];
		/** @var Project $project */
		$project = $feature->getProject();
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$implementers   = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static fn (User $u): bool => ! $project->getCanEdit()->contains($u)
										 && ! $project->getCanView()->contains($u)
										 && $feature->getAuthor()?->getId() !== $u->getId()
		);
		/** @var User $implementer */
		$implementer = $implementers[array_rand($implementers)];
		$route       = sprintf('/projects/%s/features/%s/implement', $project->getUuid()?->toString(), $feature->getUuid()?->toString());
		$this->getClient()->loginUser($implementer);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		static::assertFalse($featureRepository->find($feature->getId())?->isImplemented());
	}
	
	public function testFeatureImplementForTestUser(): void {
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		$features          = array_filter(
			$featureRepository->findAll(),
			static fn (Feature $f) => ! $f->isImplemented() && ! $f->getSoftDeleted()
		);
		/** @var Feature $feature */
		$feature = $features[array_rand($features)];
		/** @var Project $project */
		$project = $feature->getProject();
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $implementer */
		$implementer = $userRepository->findOneByRole(User::ROLE_TEST_USER);
		$route       = sprintf('/projects/%s/features/%s/implement', $project->getUuid()?->toString(), $feature->getUuid()?->toString());
		$this->getClient()->loginUser($implementer);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertFalse($featureRepository->find($feature->getId())?->isImplemented());
	}
	
	public function testFeatureFixForRegularUser(): void {
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		$features          = array_filter(
			$featureRepository->findAll(),
			static fn (Feature $f) => ! $f->isImplemented() && ! $f->getSoftDeleted()
		);
		/** @var Feature $feature */
		$feature = $features[array_rand($features)];
		/** @var Project $project */
		$project = $feature->getProject();
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $implementer */
		$implementer = $userRepository->findOneByRole(User::ROLE_USER);
		$route       = sprintf('/projects/%s/features/%s/implement', $project->getUuid()?->toString(), $feature->getUuid()?->toString());
		$this->getClient()->loginUser($implementer);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		static::assertFalse($featureRepository->find($feature->getId())?->isImplemented());
	}
	
	public function testFeatureImplementForAnon(): void {
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		$features          = array_filter(
			$featureRepository->findAll(),
			static fn (Feature $f) => ! $f->isImplemented() && ! $f->getSoftDeleted()
		);
		/** @var Feature $feature */
		$feature = $features[array_rand($features)];
		/** @var Project $project */
		$project = $feature->getProject();
		$route   = sprintf('/projects/%s/features/%s/implement', $project->getUuid()?->toString(), $feature->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
		static::assertFalse($featureRepository->find($feature->getId())?->isImplemented());
	}
}