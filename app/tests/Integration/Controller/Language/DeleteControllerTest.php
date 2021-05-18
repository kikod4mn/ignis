<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Language;

use App\Entity\Language;
use App\Entity\Role;
use App\Repository\LanguageRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\Language\DeleteController
 */
class DeleteControllerTest extends BaseWebTestCase {
	public function testSoftDelete(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$languages          = array_filter(
			$languageRepository->findAll(),
			static fn (Language $language) => ! $language->getSoftDeleted()
		);
		$language           = $languages[array_rand($languages)];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_ADMIN]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = sprintf('/languages/%s/delete', $language->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertTrue($languageRepository->find($language->getId())?->getSoftDeleted());
	}
	
	public function testDelete(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		IH::disableSoftDeleteFilter(static::$container);
		$languages = array_filter(
			$languageRepository->findAll(),
			static fn (Language $language) => $language->getSoftDeleted()
		);
		$language  = $languages[array_rand($languages)];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_ADMIN]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = sprintf('/languages/%s/delete', $language->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertNull($languageRepository->findOneBy(['uuid' => $language->getUuid()?->toString()]));
	}
	
	public function testDeleteForAnon(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$languages          = array_filter(
			$languageRepository->findAll(),
			static fn (Language $language) => ! $language->getSoftDeleted()
		);
		$language           = $languages[array_rand($languages)];
		$route              = sprintf('/languages/%s/delete', $language->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testDeleteForTestUser(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$languages          = array_filter(
			$languageRepository->findAll(),
			static fn (Language $language) => ! $language->getSoftDeleted()
		);
		$language           = $languages[array_rand($languages)];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_TEST_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = sprintf('/languages/%s/delete', $language->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertNotNull($languageRepository->find($language->getId()));
	}
	
	public function testProjectLeadCannotDelete(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$languages          = array_filter(
			$languageRepository->findAll(),
			static fn (Language $language) => ! $language->getSoftDeleted()
		);
		$language           = $languages[array_rand($languages)];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = sprintf('/languages/%s/delete', $language->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		static::assertNotNull($languageRepository->find($language->getId()));
	}
}