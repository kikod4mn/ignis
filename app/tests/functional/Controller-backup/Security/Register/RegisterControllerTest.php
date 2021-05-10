<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Security\Register;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\BaseWebTestCase;
use App\Tests\functional\Concerns\TokenManagerConcern;
use Generator;
use Symfony\Component\HttpFoundation\Request;

class RegisterControllerTest extends BaseWebTestCase {
	use TokenManagerConcern;
	
	/**
	 * Test that the registration route returns a valid webpage with a GET request
	 */
	public function testRegistrationPage(): void {
		$this->client->request(Request::METHOD_GET, '/register');
		static::assertResponseIsSuccessful();
	}
	
	/**
	 * Test that the registration route accepts valid POST data and a user is registered
	 */
	public function testRegistrationForm(): void {
		$name     = 'kiko kikoodomus';
		$email    = $this->getFaker()->email;
		$password = 'secretPASSSWORD1@';
		$user     = $this->getRepository(UserRepository::class)->findOneBy(['email' => $email]);
		static::assertNull($user);
		$token = $this->getTokenManager()->getToken('_register[_csrf_token]');
		$this->client->request(
			Request::METHOD_POST,
			'/register',
			[
				'register' => [
					'_name'          => $name,
					'_email'         => $email,
					'_plainPassword' => $password,
					'_agreeToTerms'  => 'on',
					'_token'         => $token,
				],
			]
		);
		$user = $this->getRepository(UserRepository::class)->findOneBy(['email' => $email]);
		static::assertNotNull($user);
		static::assertTrue($user instanceof User);
	}
	
	/**
	 * @dataProvider passwordProvider
	 */
	public function testRegisterFailsForPasswordViolation(string $password, string $errMsg): void {
		$token = $this->getTokenManager()->getToken('_register[_csrf_token]');
		$this->client->request(
			Request::METHOD_POST,
			'/register',
			[
				'register' => [
					'_name'          => $this->getFaker()->name,
					'_email'         => $this->getFaker()->email,
					'_plainPassword' => $password,
					'_agreeToTerms'  => 'on',
					'_token'         => $token,
				],
			]
		);
		$responseText = (string) $this->client->getResponse()->getContent();
		static::assertStringContainsStringIgnoringCase('Error!', $responseText);
		static::assertStringContainsStringIgnoringCase((string) $errMsg, $responseText);
	}
	
	/**
	 * @return Generator<int, array>
	 */
	public function passwordProvider(): Generator {
		yield [
			'password' => 'failLen1@',
			'errMsg'   => 'Password must be at least 12 characters long.',
		];
		yield [
			'password' => 'p4ssWORDF41Lf0r5ymb01',
			'errMsg'   => 'Password must contain at least one symbol $&amp;+,:;=?@#',
		];
		yield [
			'password' => 'failForNumberStill@',
			'errMsg'   => 'Password must contain at least 1 number.',
		];
		yield [
			'password' => 'f41lf0rc4p1t4ll3tt3r@',
			'errMsg'   => 'Password must contain at least 1 capital letter.',
		];
	}

//	/**
//	 * @dataProvider emailProvider
//	 */
//	public function testEmailExistsWork(?string $email, int $code, string $message): void {
//		if ($email === 'existing') {
//			$email = $this->getOneActiveUser()->getEmail();
//		}
//		$this->client->xmlHttpRequest(
//			 Request::METHOD_POST,
//			 '/register/check-email',
//			 ['email' => $email],
//			server: ['CONTENT_TYPE' => 'application/json']
//		);
//		$responseText = (string) $this->client->getResponse()->getContent();
//		static::assertResponseStatusCodeSame($code);
//		static::assertStringContainsString($message, $responseText);
//	}
//
//	/**
//	 * @return Generator<int, array>
//	 */
//	public function emailProvider(): Generator {
//		yield [
//			'email'   => 'somefunkyemail@somefunkyemail.com',
//			'code'    => Response::HTTP_OK,
//			'message' => 'Email is available.',
//		];
//		yield [
//			'email'   => 'existing',
//			'code'    => Response::HTTP_CONFLICT,
//			'message' => 'Email is already registered. Click below to reset your password.',
//		];
//		yield [
//			'email'   => null,
//			'code'    => Response::HTTP_BAD_REQUEST,
//			'message' => 'Something is broken.',
//		];
//	}
}