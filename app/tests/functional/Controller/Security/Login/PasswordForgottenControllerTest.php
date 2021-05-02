<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Security\Login;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\BaseWebTestCase;
use App\Tests\functional\Concerns\TokenManagerConcern;
use Symfony\Component\HttpFoundation\Request;

class PasswordForgottenControllerTest extends BaseWebTestCase {
	use TokenManagerConcern;
	
	public function testForgotPassword(): void {
		$this->client->request(Request::METHOD_GET, '/credentials/request/forgotten-password');
		static::assertResponseIsSuccessful();
	}
	
	public function testForgotPasswordForm(): void {
		/** @var UserRepository $userRepository */
		$userRepository = $this->getRepository(UserRepository::class);
		$user           = $userRepository->oneWithNullFields(
			['passwordResetToken', 'passwordResetTokenRequestedAt', 'passwordResetTokenRequestedFromIp']
		);
		static::assertNull($user->getPasswordResetToken());
		static::assertNull($user->getPasswordResetTokenRequestedAt());
		static::assertNull($user->getPasswordResetTokenRequestedFromIp());
		$this->client->request(
			Request::METHOD_POST,
			'/credentials/request/forgotten-password',
			[
				'password_forgotten' => [
					'_email' => $user->getEmail(),
					'_token' => $this->getTokenManager()->getToken('_password_forgotten[_csrf_token]'),
				],
			]
		);
		static::assertResponseStatusCodeSame(302);
		$this->client->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertSelectorTextContains(
			'p',
			'You have successfully requested a password reset token. Please check your email and follow the instructions. It may take some time for the email to arrive.'
		);
		/** @var User $user */
		$user = $this->getRepository(UserRepository::class)->find($user->getId());
		static::assertNotNull($user->getPasswordResetToken());
		static::assertNotNull($user->getPasswordResetTokenRequestedAt());
		static::assertNotNull($user->getPasswordResetTokenRequestedFromIp());
	}
}