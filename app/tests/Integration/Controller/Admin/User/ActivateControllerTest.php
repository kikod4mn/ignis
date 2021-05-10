<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\Admin\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IntegrationHelp;
use Symfony\Component\HttpFoundation\Request;

class ActivateControllerTest extends BaseWebTestCase {
	public function testActivate(): void {
		$this->client->loginUser(IntegrationHelp::getAdministrator(static::$container));
		$user  = IntegrationHelp::getInActiveUserWithEmailConfirmed(static::$container);
		$route = sprintf('/admin/users/%s/activate', $user->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->client->followRedirect();
		static::assertResponseIsSuccessful();
		$userRepository = IntegrationHelp::getRepository(static::$container, UserRepository::class);
		/** @var User $activeUser */
		$activeUser = $userRepository->find($user->getId());
		static::assertSame($user->getUuid()?->toString(), $activeUser->getUuid()?->toString());
		static::assertTrue($activeUser->getActive());
	}
	
	public function testActivateDoesNotWorkForTestUser(): void {
		$this->client->loginUser(IntegrationHelp::getTestUser(static::$container));
		$user  = IntegrationHelp::getInActiveUserWithEmailConfirmed(static::$container);
		$route = sprintf('/admin/users/%s/activate', $user->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(302);
		$this->client->followRedirect();
		static::assertResponseIsSuccessful();
		static::assertStringContainsStringIgnoringCase('Login', (string) $this->client->getResponse()->getContent());
		$userRepository = IntegrationHelp::getRepository(static::$container, UserRepository::class);
		/** @var User $activeUser */
		$activeUser = $userRepository->find($user->getId());
		static::assertSame($user->getUuid()?->toString(), $activeUser->getUuid()?->toString());
		static::assertFalse($activeUser->getActive());
	}
	
	public function testActivateDoesNotWorkForProjectLead(): void {
		$this->client->loginUser(IntegrationHelp::getProjectLeader(static::$container));
		$user  = IntegrationHelp::getInActiveUserWithEmailConfirmed(static::$container);
		$route = sprintf('/admin/users/%s/activate', $user->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		$userRepository = IntegrationHelp::getRepository(static::$container, UserRepository::class);
		/** @var User $activeUser */
		$activeUser = $userRepository->find($user->getId());
		static::assertSame($user->getUuid()?->toString(), $activeUser->getUuid()?->toString());
		static::assertFalse($activeUser->getActive());
	}
	
	public function testActivateDoesNotWorkForUser(): void {
		$this->client->loginUser(IntegrationHelp::getActiveUser(static::$container));
		$user  = IntegrationHelp::getInActiveUserWithEmailConfirmed(static::$container);
		$route = sprintf('/admin/users/%s/activate', $user->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		$userRepository = IntegrationHelp::getRepository(static::$container, UserRepository::class);
		/** @var User $activeUser */
		$activeUser = $userRepository->find($user->getId());
		static::assertSame($user->getUuid()?->toString(), $activeUser->getUuid()?->toString());
		static::assertFalse($activeUser->getActive());
	}
	
	public function testActivateDoesNotWorkForAnon(): void {
		$user  = IntegrationHelp::getInActiveUserWithEmailConfirmed(static::$container);
		$route = sprintf('/admin/users/%s/activate', $user->getUuid());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		$userRepository = IntegrationHelp::getRepository(static::$container, UserRepository::class);
		/** @var User $activeUser */
		$activeUser = $userRepository->find($user->getId());
		static::assertSame($user->getUuid()?->toString(), $activeUser->getUuid()?->toString());
		static::assertFalse($activeUser->getActive());
	}
}