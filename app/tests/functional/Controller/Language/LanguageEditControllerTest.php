<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Language;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use App\Tests\BaseWebTestCase;
use App\Tests\functional\Concerns\TokenManagerConcern;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

class LanguageEditControllerTest extends BaseWebTestCase {
	use TokenManagerConcern;
	
	public function testEditPage(): void {
		$user = $this->getOneAdmin();
		$this->client->loginUser($user);
		/** @var LanguageRepository $languageRepository */
		$languageRepository = $this->getRepository(LanguageRepository::class);
		$language           = $languageRepository->findAll()[0];
		$route              = sprintf('/languages/%s/edit', $language->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testEdit(): void {
		$user = $this->getOneAdmin();
		$this->client->loginUser($user);
		/** @var LanguageRepository $languageRepository */
		$languageRepository = $this->getRepository(LanguageRepository::class);
		$language           = $languageRepository->findAll()[0];
		$oldName            = $language->getName();
		$oldDescription     = $language->getDescription();
		$route              = sprintf('/languages/%s/edit', $language->getUuid());
		$token              = $this->getTokenManager()->getToken('_language_edit[_csrf_token]');
		$name               = $this->getFaker()->name;
		$description        = $this->getFaker()->paragraph;
		$this->client->request(
			Request::METHOD_POST,
			$route,
			[
				'language_edit' => [
					'_name'        => $name,
					'_description' => $description,
					'_token'       => $token,
				],
			]
		);
		$language = $languageRepository->findOneBy(['id' => $language->getId()]);
		static::assertTrue($language instanceof Language);
		static::assertTrue($language?->getName() === $name);
		static::assertTrue($language?->getDescription() === $description);
		static::assertTrue($language?->getName() !== $oldName);
		static::assertTrue($language?->getDescription() !== $oldDescription);
	}
	
	public function testEditForTestUser(): void {
		$user = $this->getTestUser();
		$this->client->loginUser($user);
		/** @var LanguageRepository $languageRepository */
		$languageRepository = $this->getRepository(LanguageRepository::class);
		$language           = $languageRepository->findAll()[0];
		$oldName            = $language->getName();
		$oldDescription     = $language->getDescription();
		$route              = sprintf('/languages/%s/edit', $language->getUuid());
		$token              = $this->getTokenManager()->getToken('_language_edit[_csrf_token]');
		$name               = $this->getFaker()->name;
		$description        = $this->getFaker()->paragraph;
		$this->client->request(
			Request::METHOD_POST,
			$route,
			[
				'language_edit' => [
					'_name'        => $name,
					'_description' => $description,
					'_token'       => $token,
				],
			]
		);
		$language = $languageRepository->findOneBy(['id' => $language->getId()]);
		static::assertTrue($language instanceof Language);
		static::assertTrue($language?->getName() !== $name);
		static::assertTrue($language?->getDescription() !== $description);
		static::assertTrue($language?->getName() === $oldName);
		static::assertTrue($language?->getDescription() === $oldDescription);
	}
}