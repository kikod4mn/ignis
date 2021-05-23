<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\User\Security;

use App\Entity\ResetPasswordRequest;
use App\Repository\ResetPasswordRequestRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

class PasswordChangeWithTokenControllerTest extends BaseWebTestCase {
	public function testPage(): void {
		/** @var ResetPasswordRequestRepository $resetRepository */
		$resetRepository = IH::getRepository(static::$container, ResetPasswordRequestRepository::class);
		$resetRequests   = array_filter(
			$resetRepository->findAll(),
			static fn (ResetPasswordRequest $r) => $r->getExpiresAt()->getTimestamp() > time()
		);
		$resetRequest    = $resetRequests[array_rand($resetRequests)];
		$route           = sprintf('/credentials/change-password/%s', $resetRequest->getSelector());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testPageDoesNotWorkForUser(): void {
		/** @var ResetPasswordRequestRepository $resetRepository */
		$resetRepository = IH::getRepository(static::$container, ResetPasswordRequestRepository::class);
		$resetRequests   = array_filter(
			$resetRepository->findAll(),
			static fn (ResetPasswordRequest $r) => $r->getExpiresAt()->getTimestamp() > time()
		);
		$resetRequest    = $resetRequests[array_rand($resetRequests)];
		$user            = $resetRequest->getUser();
		$route           = sprintf('/credentials/change-password/%s', $resetRequest->getSelector());
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
	}
	
	public function testForm(): void {
		/** @var ResetPasswordRequestRepository $resetRepository */
		$resetRepository = IH::getRepository(static::$container, ResetPasswordRequestRepository::class);
		$resetRequests   = array_filter(
			$resetRepository->findAll(),
			static fn (ResetPasswordRequest $r) => $r->getExpiresAt()->getTimestamp() > time()
		);
		$resetRequest    = $resetRequests[array_rand($resetRequests)];
		$user            = $resetRequest->getUser();
		$oldPwdHash      = $user->getPassword();
		$newPwd          = 'SuperStaticSecret1@###';
		$route           = sprintf('/credentials/change-password/%s', $resetRequest->getSelector());
		$data            = [
			'password_change_with_token' => [
				'_password' => $newPwd,
				'_token'    => IH::getCsrf(static::$container)->getToken('_password_change_with_token[_csrf_token]'),
			],
		];
		$this->getClient()->request(Request::METHOD_POST, $route, $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertContains($oldPwdHash, $user->getOldPasswordHashes());
		static::assertNotEquals($oldPwdHash, $user->getPassword());
		static::assertNotEquals($newPwd, $user->getPassword());
	}
}