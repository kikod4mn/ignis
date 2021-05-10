<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Language;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use App\Tests\BaseWebTestCase;
use App\Tests\functional\Concerns\TokenManagerConcern;
use Symfony\Component\HttpFoundation\Request;
use function dd;

class LanguageCreateControllerTest extends BaseWebTestCase {
	use TokenManagerConcern;
	
	public function testCreatePage(): void {
		$user = $this->getOneAdmin();
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, '/languages/create');
		static::assertResponseIsSuccessful();
	}
	
	public function testCreate(): void {
		$token       = $this->getTokenManager()->getToken('_language_create[_csrf_token]');
		$user        = $this->getOneAdmin();
		$name        = $this->getFaker()->unique()->words(2, true);
		$description = $this->getFaker()->unique()->paragraph;
		$this->client->loginUser($user);
		$this->client->request(
			Request::METHOD_POST,
			'/languages/create',
			[
				'language_create' => [
					'_name'        => $name,
					'_description' => $description,
					'_token'       => $token,
				],
			]
		);
		/** @var LanguageRepository $LanguageRepository */
		$LanguageRepository = $this->getRepository(LanguageRepository::class);
		$language           = $LanguageRepository->findOneBy(['name' => $name]);
		static::assertTrue($language instanceof Language);
		if ($language?->getDescription() !== $description) {
			dd($language?->getDescription(), $description);
		}
		static::assertTrue($language?->getDescription() === $description);
	}
	
	public function testCreateForTestUser(): void {
		$token       = $this->getTokenManager()->getToken('_language_create[_csrf_token]');
		$user        = $this->getTestUser();
		$name        = $this->getFaker()->unique()->words(2, true);
		$description = $this->getFaker()->unique()->paragraph;
		$this->client->loginUser($user);
		$this->client->request(
			Request::METHOD_POST,
			'/languages/create',
			[
				'language_create' => [
					'_name'        => $name,
					'_description' => $description,
					'_token'       => $token,
				],
			]
		);
		/** @var LanguageRepository $LanguageRepository */
		$LanguageRepository = $this->getRepository(LanguageRepository::class);
		$language           = $LanguageRepository->findOneBy(['name' => $name]);
		static::assertNull($language);
	}
}