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
 * @covers \App\Controller\Language\CreateController
 */
class CreateControllerTest extends BaseWebTestCase {
	public function testCreatePage(): void {
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_ADMIN]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = '/languages/create';
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testCreatePageForAnon(): void {
		$route = '/languages/create';
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
	
	public function testCreate(): void {
		$name        = sprintf('%s %s', $this->getFaker()->word, $this->getFaker()->word);
		$description = $this->getFaker()->paragraph;
		$data        = [
			'language_create' => [
				'_name'        => $name,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_language_create[_csrf_token]'),
			],
		];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_ADMIN]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = '/languages/create';
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
	
	public function testCreateForTestUser(): void {
		$name        = sprintf('%s %s', $this->getFaker()->word, $this->getFaker()->word);
		$description = $this->getFaker()->paragraph;
		$data        = [
			'language_create' => [
				'_name'        => $name,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_language_create[_csrf_token]'),
			],
		];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_TEST_USER]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = '/languages/create';
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$language           = $languageRepository->findOneBy(['name' => $name, 'description' => $description]);
		static::assertNull($language);
	}
	
	public function testProjectLeadCannotCreate(): void {
		$name        = sprintf('%s %s', $this->getFaker()->word, $this->getFaker()->word);
		$description = $this->getFaker()->paragraph;
		$data        = [
			'language_create' => [
				'_name'        => $name,
				'_description' => $description,
				'_token'       => IH::getCsrf(static::$container)->getToken('_language_create[_csrf_token]'),
			],
		];
		/** @var Role $role */
		$role = IH::getRepository(static::$container, RoleRepository::class)->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]);
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$users          = $userRepository->findByRoles([$role]);
		$creator        = $users[array_rand($users)];
		$route          = '/languages/create';
		$this->getClient()->loginUser($creator);
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(404);
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$language           = $languageRepository->findOneBy(['name' => $name, 'description' => $description]);
		static::assertNull($language);
	}
}