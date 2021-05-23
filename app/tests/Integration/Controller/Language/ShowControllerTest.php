<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Language;

use App\Entity\User;
use App\Repository\LanguageRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \App\Controller\Language\ShowController
 */
class ShowControllerTest extends BaseWebTestCase {
	public function testShowWorksForAdmin(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$languages         = $languageRepository->findAll();
		$language           = $languages[array_rand($languages)];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user        = $userRepository->findOneByRole(User::ROLE_ADMIN);
		$route = sprintf('/languages/%s/show', $language->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testShowWorksForTestUser(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$languages         = $languageRepository->findAll();
		$language           = $languages[array_rand($languages)];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user        = $userRepository->findOneByRole(User::ROLE_TEST_USER);
		$route = sprintf('/languages/%s/show', $language->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testShowWorksForProjectLead(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$languages         = $languageRepository->findAll();
		$language           = $languages[array_rand($languages)];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user        = $userRepository->findOneByRole(User::ROLE_PROJECT_LEAD);
		$route = sprintf('/languages/%s/show', $language->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testShowDoesNotWorkForRegularUser(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$languages         = $languageRepository->findAll();
		$language           = $languages[array_rand($languages)];
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user        = $userRepository->findOneByRole(User::ROLE_USER);
		$route = sprintf('/languages/%s/show', $language->getUuid()?->toString());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
	}
	
	public function testShowDoesNotWorkForAnon(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = IH::getRepository(static::$container, LanguageRepository::class);
		$languages         = $languageRepository->findAll();
		$language           = $languages[array_rand($languages)];
		$route              = sprintf('/languages/%s/show', $language->getUuid()?->toString());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->getClient()->getResponse()->getContent());
	}
}