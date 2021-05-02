<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Language;

use App\Repository\LanguageRepository;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

/**
 * @covers \App\Controller\Language\ShowController
 */
class LanguageShowControllerTest extends BaseWebTestCase {
	public function testShow(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = $this->getRepository(LanguageRepository::class);
		$language           = $languageRepository->findAll()[0];
		$user               = $this->getOneProjectLead();
		$this->client->loginUser($user);
		$route = sprintf('/languages/%s/show', $language->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testShowForTestUser(): void {
		/** @var LanguageRepository $languageRepository */
		$languageRepository = $this->getRepository(LanguageRepository::class);
		$language           = $languageRepository->findAll()[0];
		$user               = $this->getTestUser();
		$this->client->loginUser($user);
		$route = sprintf('/languages/%s/show', $language->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
}