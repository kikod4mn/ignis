<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Security\Register;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

class EmailConfirmControllerTest extends BaseWebTestCase {
	public function testEmailConfirmation(): void {
		/** @var UserRepository $userRepository */
		$userRepository = $this->getRepository(UserRepository::class);
		$user           = $userRepository->oneWithNotNullFields(['emailConfirmToken']);
		static::assertNotNull($user->getEmailConfirmToken());
		static::assertNull($user->getEmailConfirmedAt());
		$this->client->request(Request::METHOD_GET, sprintf('/credentials/confirm/email/%s', $user->getEmailConfirmToken()));
		/** @var User $user */
		$user = $this->getRepository(UserRepository::class)->find($user->getId());
		static::assertNull($user->getEmailConfirmToken());
		static::assertNotNull($user->getEmailConfirmedAt());
	}
}