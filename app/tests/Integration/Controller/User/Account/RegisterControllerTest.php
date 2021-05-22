<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\User\Account;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

class RegisterControllerTest extends BaseWebTestCase {
	public function testPage(): void {
		$this->getClient()->request(Request::METHOD_GET, '/register');
		static::assertResponseIsSuccessful();
	}
	
	public function testPageDoesNotWorkForUser(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		/** @var User $user */
		$user = $userRepository->find(1);
		$this->getClient()->loginUser($user);
		$this->getClient()->request(Request::METHOD_GET, '/register');
		static::assertResponseStatusCodeSame(404);
	}
	
	public function testRegisterSuccess(): void {
		/** @var UserRepository $userRepository */
		$userRepository = IH::getRepository(static::$container, UserRepository::class);
		$name           = $this->getFaker()->name;
		$email          = $this->getFaker()->email;
		$password       = 'SuperStaticExtra4w3s0m3@';
		$data           = [
			'register' => [
				'_name'          => $name,
				'_email'         => $email,
				'_plainPassword' => $password,
				'_agreeToTerms'  => true,
				'_token'         => IH::getCsrf(static::$container)->getToken('_register[_csrf_token]'),
			],
		];
		$this->getClient()->request(Request::METHOD_POST, '/register', $data);
		$user = $userRepository->findOneBy(['name' => $name, 'email' => $email]);
		static::assertNotNull($user);
		static::assertNotEquals($password, $user->getPassword());
	}
}