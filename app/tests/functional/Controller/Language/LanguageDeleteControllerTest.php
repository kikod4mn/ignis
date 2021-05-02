<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Language;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

class LanguageDeleteControllerTest extends BaseWebTestCase {
	public function testDelete(): void {
		$user = $this->getOneAdmin();
		/** @var LanguageRepository $LanguageRepository */
		$LanguageRepository = $this->getRepository(LanguageRepository::class);
		$language           = $LanguageRepository->findAll()[0];
		$route              = sprintf('/languages/%s/delete', $language->getUuid());
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, $route);
		$language = $LanguageRepository->findOneBy(['uuid' => $language->getUuid()]);
		static::assertNull($language);
	}
	
	public function testDeleteForTestUser(): void {
		$user = $this->getTestUser();
		/** @var LanguageRepository $LanguageRepository */
		$LanguageRepository = $this->getRepository(LanguageRepository::class);
		$language           = $LanguageRepository->findAll()[0];
		$route              = sprintf('/languages/%s/delete', $language->getUuid());
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, $route);
		$language = $LanguageRepository->findOneBy(['uuid' => $language->getUuid()]);
		static::assertTrue($language instanceof Language);
	}
}