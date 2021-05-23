<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Feature;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\FeatureRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Feature\CreateController
 */
class CreateControllerTest extends BaseWebTestCase {
	public function testCreatePage(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$creators       = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static fn (User $u) => ! $u->getProjects()->isEmpty()
		);
		/** @var User $creator */
		$creator = $creators[array_rand($creators)];
		/** @var Project $project */
		$project = $creator->getProjects()[array_rand($creator->getProjects()->toArray())];
		$route   = sprintf('/projects/%s/features/create', $project->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testCreatePageForAnon(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$creators       = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static fn (User $u) => ! $u->getProjects()->isEmpty()
		);
		/** @var User $creator */
		$creator = $creators[array_rand($creators)];
		/** @var Project $project */
		$project = $creator->getProjects()[array_rand($creator->getProjects()->toArray())];
		$route   = sprintf('/projects/%s/features/create', $project->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testCreateForProjectAuthor(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$creators       = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static fn (User $u) => ! $u->getProjects()->isEmpty()
		);
		/** @var User $creator */
		$creator = $creators[array_rand($creators)];
		/** @var Project $project */
		$project     = $creator->getProjects()[array_rand($creator->getProjects()->toArray())];
		$route       = sprintf('/projects/%s/features/create', $project->getUuid()?->toString());
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'feature_create' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_feature_create[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		static::assertNotNull($featureRepository->findOneBy(compact('title', 'description', 'project')));
	}
	
	public function testCreateForProjectLeadButNotProjectAuthor(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$creators       = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static fn (User $u) => ! $u->getProjects()->isEmpty()
		);
		/** @var User $creator */
		$creator = $creators[array_rand($creators)];
		/** @var Project $project */
		$project     = $creator->getProjects()[array_rand($creator->getProjects()->toArray())];
		$route       = sprintf('/projects/%s/features/create', $project->getUuid()?->toString());
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'feature_create' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_feature_create[_csrf_token]'),
			],
		];
		$notCreators = array_filter(
			$creators,
			static fn (User $u) => $u->getId() !== $creator->getId()
								   && ! $project->getCanEdit()->contains($u)
								   && ! $project->getCanView()->contains($u)
		);
		/** @var User $notCreator */
		$notCreator = $notCreators[array_rand($notCreators)];
		$this->getClient()->loginUser($notCreator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		static::assertNull($featureRepository->findOneBy(compact('title', 'description', 'project')));
	}
	
	public function testCreateForRegularUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$creators       = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static fn (User $u) => ! $u->getProjects()->isEmpty()
		);
		/** @var User $creator */
		$creator = $creators[array_rand($creators)];
		/** @var Project $project */
		$project     = $creator->getProjects()[array_rand($creator->getProjects()->toArray())];
		$route       = sprintf('/projects/%s/features/create', $project->getUuid()?->toString());
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'feature_create' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_feature_create[_csrf_token]'),
			],
		];
		$notCreators = array_filter(
			$userRepository->findByRole(User::ROLE_USER),
			static fn (User $u) => $u->getId() !== $creator->getId()
		);
		/** @var User $notCreator */
		$notCreator = $notCreators[array_rand($notCreators)];
		$this->getClient()->loginUser($notCreator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		static::assertNull($featureRepository->findOneBy(compact('title', 'description', 'project')));
	}
	
	public function testCreateForTestUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$creators       = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static fn (User $u) => ! $u->getProjects()->isEmpty()
		);
		/** @var User $creator */
		$creator = $creators[array_rand($creators)];
		/** @var Project $project */
		$project     = $creator->getProjects()[array_rand($creator->getProjects()->toArray())];
		$route       = sprintf('/projects/%s/features/create', $project->getUuid()?->toString());
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'feature_create' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_feature_create[_csrf_token]'),
			],
		];
		$notCreators = array_filter(
			$userRepository->findByRole(User::ROLE_TEST_USER),
			static fn (User $u) => $u->getId() !== $creator->getId()
		);
		/** @var User $notCreator */
		$notCreator = $notCreators[array_rand($notCreators)];
		$this->getClient()->loginUser($notCreator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		static::assertNull($featureRepository->findOneBy(compact('title', 'description', 'project')));
	}
	
	public function testCreateForAnon(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$creators       = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static fn (User $u) => ! $u->getProjects()->isEmpty()
		);
		/** @var User $creator */
		$creator = $creators[array_rand($creators)];
		/** @var Project $project */
		$project     = $creator->getProjects()[array_rand($creator->getProjects()->toArray())];
		$route       = sprintf('/projects/%s/features/create', $project->getUuid()?->toString());
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'feature_create' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_feature_create[_csrf_token]'),
			],
		];
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		static::assertNull($featureRepository->findOneBy(compact('title', 'description', 'project')));
	}
}