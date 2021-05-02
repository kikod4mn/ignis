<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Security\Login;

use App\Repository\UserRepository;
use App\Tests\BaseWebTestCase;
use App\Tests\functional\Concerns\TokenManagerConcern;
use Generator;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

class PasswordChangeWithTokenControllerTest extends BaseWebTestCase {
	use TokenManagerConcern;
	
	public function testChangePassword(): void {
		/** @var UserRepository $userRepository */
		$userRepository = $this->getRepository(UserRepository::class);
		$user           = $userRepository->oneWithNotNullFields(['passwordResetToken']);
		$route          = sprintf('/credentials/change-password/%s', $user->getPasswordResetToken());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	/**
	 * @dataProvider passwordProvider
	 */
	public function testChangePasswordForm(string $password, bool $expected, ?string $errMsg): void {
		/** @var UserRepository $userRepository */
		$userRepository = $this->getRepository(UserRepository::class);
		$user           = $userRepository->oneWithNotNullFields(['passwordResetToken']);
		$route          = sprintf('/credentials/change-password/%s', $user->getPasswordResetToken());
		static::assertNotNull($user->getPasswordResetToken());
		static::assertNotNull($user->getPasswordResetTokenRequestedAt());
		static::assertNotNull($user->getPasswordResetTokenRequestedFromIp());
		$tokenPwdChange = $this->getTokenManager()->getToken('_password_change_with_token[_csrf_token]');
		// note token must be pre request for some reason. I am stupid now, dont know why. Maybe future me will know...
		$tokenLogin = $this->getTokenManager()->getToken('_security_login[_csrf_token]');
		$this->client
			->request(
				Request::METHOD_POST,
				$route,
				[
					'password_change_with_token' => [
						'_token'    => $tokenPwdChange,
						'_password' => $password,
					],
				]
			);
		if ($expected) {
			$this->client->request(
				Request::METHOD_POST,
				'/login',
				[
					'security_login' => [
						'_token'    => $tokenLogin,
						'_email'    => $user->getEmail(),
						'_password' => $password,
					],
				]
			);
			static::assertResponseStatusCodeSame(302);
			$this->client->followRedirect();
			static::assertResponseIsSuccessful();
			static::assertStringContainsStringIgnoringCase('Welcome back!', (string) $this->client->getResponse()->getContent());
		} else {
			static::assertStringContainsStringIgnoringCase(
				(string) $errMsg,
				(string) $this->client->getResponse()->getContent()
			);
			$this->client->request(
				Request::METHOD_POST,
				'/login',
				[
					'security_login' => [
						'_token'    => $tokenLogin,
						'_email'    => $user->getEmail(),
						'_password' => $password,
					],
				]
			);
			static::assertResponseStatusCodeSame(302);
			$this->client->followRedirect();
			static::assertResponseIsSuccessful();
			static::assertStringContainsStringIgnoringCase('Error!', (string) $this->client->getResponse()->getContent());
		}
	}
	
	/**
	 * @return Generator<int, array>
	 */
	public function passwordProvider(): Generator {
		yield [
			'password' => '@SuperSecretPwdChanged123@',
			'expected' => true,
			'errMsg'   => null,
		];
		yield [
			'password' => 'ssssssssssssssssssssA@',
			'expected' => false,
			'errMsg'   => 'Password must contain at least 1 number.',
		];
		yield [
			'password' => 'ssssssssssssssssssss1@',
			'expected' => false,
			'errMsg'   => 'Password must contain at least 1 capital letter.',
		];
		yield [
			'password' => 'ssssssssssssssssssss1A',
			'expected' => false,
			'errMsg'   => 'Password must contain at least one symbol $&amp;+,:;=?@#',
		];
	}
}