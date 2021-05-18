<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Feature;

use App\Entity\Feature;
use App\Entity\Project;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\FeatureRepository;
use App\Repository\ProjectRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Feature\DeleteController
 */
class DeleteControllerTest extends BaseWebTestCase {
	public function testFeatureSoftDelete(): void {
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		$features          = array_filter(
			$featureRepository->findAll(),
			static fn (Feature $f) => ! $f->getSoftDeleted() && ! $f->getProject()?->getSoftDeleted()
		);
		$feature           = $features[array_rand($features)];
		static::assertFalse($feature->getSoftDeleted());
		/** @var Project $project */
		$project = $feature->getProject();
		/** @var User $author */
		$author = $feature->getAuthor();
		$route  = sprintf(
			'/projects/%s/features/%s/delete',
			$project->getUuid()?->toString(), $feature->getUuid()?->toString()
		);
		static::assertTrue($project->getFeatures()->contains($feature));
		$this->getClient()->loginUser($author);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		IH::disableSoftDeleteFilter(static::$container);
		$feature = $featureRepository->find($feature->getId());
		static::assertNotNull($feature);
		static::assertSame($feature->getProject()?->getId(), $project->getId());
		static::assertTrue($feature->getSoftDeleted());
		static::assertNotNull($feature->getSoftDeletedAt());
	}
	
	public function testFeatureDelete(): void {
		IH::disableSoftDeleteFilter(static::$container);
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		$features          = array_filter(
			$featureRepository->findAll(), static fn (Feature $b) => $b->getSoftDeleted()
																	 && $b->getSoftDeletedAt() instanceof DateTimeInterface
																	 && ! $b->getProject()?->getSoftDeleted()
		);
		$feature           = $features[array_rand($features)];
		static::assertTrue($feature->getSoftDeleted());
		static::assertNotNull($feature->getSoftDeletedAt());
		/** @var Project $project */
		$project = $feature->getProject();
		/** @var User $author */
		$author = $feature->getAuthor();
		$route  = sprintf(
			'/projects/%s/features/%s/delete',
			$project->getUuid()?->toString(), $feature->getUuid()?->toString()
		);
		static::assertTrue($project->getFeatures()->contains($feature));
		$this->getClient()->loginUser($author);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var Project $project */
		$project = IH::getRepository(static::$container, ProjectRepository::class)
					 ->findOneBy(['uuid' => $project->getUuid()?->toString()])
		;
		static::assertFalse($project->getFeatures()->contains($feature));
		IH::disableSoftDeleteFilter(static::$container);
		/** @var ?Project $project */
		$project = IH::getRepository(static::$container, ProjectRepository::class)->find($project->getId());
		/** @var ?Feature $feature */
		$feature = $featureRepository->findOneBy(['title' => $feature->getTitle(), 'description' => $feature->getDescription()]);
		static::assertNotNull($project);
		static::assertNull($feature);
	}
	
	public function testFeatureDeleteForTestUser(): void {
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		$features          = array_filter(
			$featureRepository->findAll(),
			static fn (Feature $b) => ! $b->getSoftDeleted() && ! $b->getProject()?->getSoftDeleted()
		);
		$feature           = $features[array_rand($features)];
		/** @var Project $project */
		$project = $feature->getProject();
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_TEST_USER]);
		/** @var User $testUser */
		$testUser = $userRepository->findOneByRoles([$role]);
		$route    = sprintf(
			'/projects/%s/features/%s/delete',
			$project->getUuid()?->toString(), $feature->getUuid()?->toString()
		);
		static::assertTrue($project->getFeatures()->contains($feature));
		$this->getClient()->loginUser($testUser);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var Project $project */
		$project = IH::getRepository(static::$container, ProjectRepository::class)->find($project->getId());
		$feature = $featureRepository->find($feature->getId());
		static::assertSame($feature?->getProject()?->getId(), $project->getId());
		static::assertInstanceOf(Feature::class, $feature);
	}
	
	public function testFeatureDeleteDoesNotWorkForNotAuthor(): void {
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		$features          = array_filter(
			$featureRepository->findAll(), static fn (Feature $b) => ! $b->getSoftDeleted()
																	 && ! $b->getProject()?->getSoftDeleted()
		);
		$feature           = $features[array_rand($features)];
		/** @var Project $project */
		$project = $feature->getProject();
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var Role $role */
		$role  = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_USER]);
		$users = array_filter(
			$userRepository->findByRoles([$role]),
			static fn (User $u): bool => ! $project->getCanView()->contains($u)
										 && ! $project->getCanEdit()->contains($u)
										 && $project->getAuthor()?->getId() !== $u->getId()
		);
		/** @var User $user */
		$user  = $users[array_rand($users)];
		$route = sprintf(
			'/projects/%s/features/%s/delete',
			$project->getUuid()?->toString(), $feature->getUuid()?->toString()
		);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		/** @var Project $project */
		$project = IH::getRepository(static::$container, ProjectRepository::class)->find($project->getId());
		/** @var ?Feature $feature */
		$feature = $featureRepository->find($feature->getId());
		static::assertSame($feature?->getProject()?->getId(), $project->getId());
		static::assertInstanceOf(Feature::class, $feature);
	}
	
	public function testFeatureDeleteDoesNotWorkForAnon(): void {
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		$features          = array_filter(
			$featureRepository->findAll(),
			static fn (Feature $b) => ! $b->getSoftDeleted() && ! $b->getProject()?->getSoftDeleted()
		);
		$feature           = $features[array_rand($features)];
		/** @var Project $project */
		$project = $feature->getProject();
		$route   = sprintf(
			'/projects/%s/features/%s/delete',
			$project->getUuid()?->toString(), $feature->getUuid()?->toString()
		);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
		/** @var Project $project */
		$project = IH::getRepository(static::$container, ProjectRepository::class)->find($project->getId());
		/** @var ?Feature $feature */
		$feature = IH::getRepository(static::$container, FeatureRepository::class)->find($feature->getId());
		static::assertSame($feature?->getProject()?->getId(), $project->getId());
		static::assertInstanceOf(Feature::class, $feature);
	}
}