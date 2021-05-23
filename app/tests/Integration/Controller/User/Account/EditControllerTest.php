<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\User\Account;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\User\Account\EditController
 */
class EditControllerTest extends BaseWebTestCase {
	public function testEditPage(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->findOneByRole(User::ROLE_USER);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/account/edit');
		static::assertResponseIsSuccessful();
	}
	
	public function testEditSubmission(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user       = $userRepository->findOneByRole(User::ROLE_USER);
		$newEmail   = $this->getFaker()->email;
		$oldEmail   = $user->getEmail();
		$oldPwdHash = $user->getPassword();
		$newPwd     = 'secretiveAS$h1t!!!1one';
		static::assertNotSame($oldEmail, $newEmail);
		static::assertNotContains($oldEmail, $user->getOldEmails());
		static::assertNotContains($oldPwdHash, $user->getOldPasswordHashes());
		$data = [
			'edit' => [
				'_email'         => $newEmail,
				'_plainPassword' => $newPwd,
				'_token'         => IH::getCsrf(static::$container)->getToken('_account_edit[_csrf_token]'),
			],
		];
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_POST, '/account/edit', $data);
		static::assertResponseStatusCodeSame(302);
		$this->getClient()->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertSame($user->getEmail(), $newEmail);
		static::assertNotSame($oldPwdHash, $user->getPassword());
		static::assertNotSame($newPwd, $user->getPassword());
		static::assertNull($user->getPlainPassword());
		static::assertContains($oldEmail, $user->getOldEmails());
		static::assertContains($oldPwdHash, $user->getOldPasswordHashes());
	}
}