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
 * @covers \App\Controller\Language\EditController
 */
class EditControllerTest extends BaseWebTestCase {
	public function testEditPage(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_ADMIN]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$languages          = $languageRepository->findAll();
		$language           = $languages[array_rand($languages)];
		$route              = sprintf('/languages/%s/edit', $language->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testEditPageForAnon(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$languages          = $languageRepository->findAll();
		$language           = $languages[array_rand($languages)];
		$route              = sprintf('/languages/%s/edit', $language->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testEdit(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$languages          = $languageRepository->findAll();
		$language           = $languages[array_rand($languages)];
		$name               = sprintf('%s %s', $this->getFaker()->word, $this->getFaker()->word);
		$description        = $this->getFaker()->paragraph;
		$data               = [
			'language_edit' => [
				'_name'        => $name,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_language_edit[_csrf_token]'),
			],
		];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_ADMIN]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = sprintf('/languages/%s/edit', $language->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$language           = $languageRepository->findOneBy(['name' => $name, 'description' => $description]);
		static::assertInstanceOf(Language::class, $language);
	}
	
	public function testEditForTestUser(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$languages          = $languageRepository->findAll();
		$language           = $languages[array_rand($languages)];
		$name               = sprintf('%s %s', $this->getFaker()->word, $this->getFaker()->word);
		$description        = $this->getFaker()->paragraph;
		$data               = [
			'language_edit' => [
				'_name'        => $name,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_language_edit[_csrf_token]'),
			],
		];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_TEST_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = sprintf('/languages/%s/edit', $language->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseIsSuccessful();
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$language           = $languageRepository->findOneBy(['name' => $name, 'description' => $description]);
		static::assertNull($language);
	}
	
	public function testProjectLeadCannotEdit(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$languages          = $languageRepository->findAll();
		$language           = $languages[array_rand($languages)];
		$name               = sprintf('%s %s', $this->getFaker()->word, $this->getFaker()->word);
		$description        = $this->getFaker()->paragraph;
		$data               = [
			'language_edit' => [
				'_name'        => $name,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_language_edit[_csrf_token]'),
			],
		];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = sprintf('/languages/%s/edit', $language->getUuid()?->toString());
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$language           = $languageRepository->findOneBy(['name' => $name, 'description' => $description]);
		static::assertNull($language);
	}
}