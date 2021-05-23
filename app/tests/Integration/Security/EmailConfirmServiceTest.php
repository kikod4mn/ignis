<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\ConfirmEmailService;
use App\Service\TimeCreator;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;

class EmailConfirmServiceTest extends BaseWebTestCase {
	public function testSetConfirmationForUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$user           = new User();
		$emailConfirmer = new ConfirmEmailService($userRepository);
		$emailConfirmer->createConfirmRequest($user);
		static::assertNull($user->getEmailConfirmedAt());
		static::assertNotNull($user->getEmailConfirmToken());
	}
	
	public function testEmailVerifierWithRealUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneBy(['emailConfirmedAt' => null]);
		$user->setEmailConfirmationTokenExpiresAt(TimeCreator::randomFuture(1));
		$emailConfirmer = new ConfirmEmailService($userRepository);
		static::assertTrue($emailConfirmer->verifyAndConfirm((string) $user->getEmailConfirmToken()));
		static::assertNotNull($user->getEmailConfirmedAt());
		static::assertNull($user->getEmailConfirmToken());
	}
	
	public function testEmailVerifierWithRealUserAlreadyConfirmedEmail(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user           = $userRepository->findOneBy(['emailConfirmToken' => null]);
		$emailConfirmer = new ConfirmEmailService($userRepository);
		static::assertFalse($emailConfirmer->verifyAndConfirm((string) $user->getEmailConfirmToken()));
		static::assertNotNull($user->getEmailConfirmedAt());
		static::assertNull($user->getEmailConfirmToken());
	}
	
	public function testEmailVerifierWithNotRealUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$emailConfirmer = new ConfirmEmailService($userRepository);
		static::assertFalse($emailConfirmer->verifyAndConfirm('what-email@no-email.xyz'));
	}
}