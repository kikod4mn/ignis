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
 * @covers \App\Controller\Language\EditController
 */
class EditControllerTest extends BaseWebTestCase {
	public function testEditPage(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$creators       = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u): bool {
				if ($u->getProjects()->isEmpty()) {
					return false;
				}
				foreach ($u->getProjects() as $p) {
					if ($p->getFeatures()->isEmpty()) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $creator */
		$creator  = $creators[array_rand($creators)];
		$projects = array_filter($creator->getProjects()->toArray(), static fn (Project $p) => ! $p->getFeatures()->isEmpty());
		/** @var Project $project */
		$project = $projects[array_rand($projects)];
		/** @var Feature $feature */
		$feature = $project->getFeatures()[array_rand($project->getFeatures()->toArray())];
		$route   = sprintf('/projects/%s/features/%s/edit', $project->getUuid()?->toString(), $feature->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testEditPageForAnon(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$creators       = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u): bool {
				if ($u->getProjects()->isEmpty()) {
					return false;
				}
				foreach ($u->getProjects() as $p) {
					if ($p->getFeatures()->isEmpty()) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $creator */
		$creator  = $creators[array_rand($creators)];
		$projects = array_filter($creator->getProjects()->toArray(), static fn (Project $p) => ! $p->getFeatures()->isEmpty());
		/** @var Project $project */
		$project = $projects[array_rand($projects)];
		/** @var Feature $feature */
		$feature = $project->getFeatures()[array_rand($project->getFeatures()->toArray())];
		$route   = sprintf('/projects/%s/features/%s/edit', $project->getUuid()?->toString(), $feature->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testEditForProjectAuthor(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$creators       = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u): bool {
				if ($u->getProjects()->isEmpty()) {
					return false;
				}
				foreach ($u->getProjects() as $p) {
					if ($p->getFeatures()->isEmpty()) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $creator */
		$creator  = $creators[array_rand($creators)];
		$projects = array_filter($creator->getProjects()->toArray(), static fn (Project $p) => ! $p->getFeatures()->isEmpty());
		/** @var Project $project */
		$project = $projects[array_rand($projects)];
		/** @var Feature $feature */
		$feature     = $project->getFeatures()[array_rand($project->getFeatures()->toArray())];
		$route       = sprintf('/projects/%s/features/%s/edit', $project->getUuid()?->toString(), $feature->getUuid()?->toString());
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'feature_edit' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_feature_edit[_csrf_token]'),
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
	
	public function testEditForProjectLeadButNotProjectAuthor(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$creators       = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u): bool {
				if ($u->getProjects()->isEmpty()) {
					return false;
				}
				foreach ($u->getProjects() as $p) {
					if ($p->getFeatures()->isEmpty()) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $creator */
		$creator  = $creators[array_rand($creators)];
		$projects = array_filter($creator->getProjects()->toArray(), static fn (Project $p) => ! $p->getFeatures()->isEmpty());
		/** @var Project $project */
		$project = $projects[array_rand($projects)];
		/** @var Feature $feature */
		$feature     = $project->getFeatures()[array_rand($project->getFeatures()->toArray())];
		$route       = sprintf('/projects/%s/features/%s/edit', $project->getUuid()?->toString(), $feature->getUuid()?->toString());
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'feature_edit' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_feature_edit[_csrf_token]'),
			],
		];
		$notCreators = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
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
	
	public function testEditForRegularUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$creators       = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u): bool {
				if ($u->getProjects()->isEmpty()) {
					return false;
				}
				foreach ($u->getProjects() as $p) {
					if ($p->getFeatures()->isEmpty()) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $creator */
		$creator  = $creators[array_rand($creators)];
		$projects = array_filter($creator->getProjects()->toArray(), static fn (Project $p) => ! $p->getFeatures()->isEmpty());
		/** @var Project $project */
		$project = $projects[array_rand($projects)];
		/** @var Feature $feature */
		$feature     = $project->getFeatures()[array_rand($project->getFeatures()->toArray())];
		$route       = sprintf('/projects/%s/features/%s/edit', $project->getUuid()?->toString(), $feature->getUuid()?->toString());
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'feature_edit' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_feature_edit[_csrf_token]'),
			],
		];
		$notCreators = array_filter(
			$userRepository->findByRole(User::ROLE_USER),
			static fn (User $u) => $u->getId() !== $creator->getId() && ! $project->getCanEdit()->contains($u)
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
	
	public function testEditForTestUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$creators       = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u): bool {
				if ($u->getProjects()->isEmpty()) {
					return false;
				}
				foreach ($u->getProjects() as $p) {
					if ($p->getFeatures()->isEmpty()) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $creator */
		$creator  = $creators[array_rand($creators)];
		$projects = array_filter($creator->getProjects()->toArray(), static fn (Project $p) => ! $p->getFeatures()->isEmpty());
		/** @var Project $project */
		$project = $projects[array_rand($projects)];
		/** @var Feature $feature */
		$feature     = $project->getFeatures()[array_rand($project->getFeatures()->toArray())];
		$route       = sprintf('/projects/%s/features/%s/edit', $project->getUuid()?->toString(), $feature->getUuid()?->toString());
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'feature_edit' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_feature_edit[_csrf_token]'),
			],
		];
		$notCreators  = array_filter(
			$userRepository->findByRole(User::ROLE_TEST_USER),
			static fn (User $u) => $u->getId() !== $creator->getId()
		);
		/** @var User $notCreator */
		$notCreator = $notCreators[array_rand($notCreators)];
		$this->getClient()->loginUser($notCreator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseIsSuccessful();
		/** @var FeatureRepository $featureRepository */
		$featureRepository = IH::getRepository(static::$container, FeatureRepository::class);
		static::assertNull($featureRepository->findOneBy(compact('title', 'description', 'project')));
	}
	
	public function testEditForAnon(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$creators       = array_filter(
			$userRepository->findByRole(User::ROLE_PROJECT_LEAD),
			static function (User $u): bool {
				if ($u->getProjects()->isEmpty()) {
					return false;
				}
				foreach ($u->getProjects() as $p) {
					if ($p->getFeatures()->isEmpty()) {
						return false;
					}
				}
				return true;
			}
		);
		/** @var User $creator */
		$creator  = $creators[array_rand($creators)];
		$projects = array_filter($creator->getProjects()->toArray(), static fn (Project $p) => ! $p->getFeatures()->isEmpty());
		/** @var Project $project */
		$project = $projects[array_rand($projects)];
		/** @var Feature $feature */
		$feature     = $project->getFeatures()[array_rand($project->getFeatures()->toArray())];
		$route       = sprintf('/projects/%s/features/%s/edit', $project->getUuid()?->toString(), $feature->getUuid()?->toString());
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->paragraph;
		$data        = [
			'feature_edit' => [
				'_title'       => $title,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_feature_edit[_csrf_token]'),
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