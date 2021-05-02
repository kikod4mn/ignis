<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Admin\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

class ActivationControllerTest extends BaseWebTestCase {
	public function testActivate(): void {
		/** @var UserRepository $userRepository */
		$userRepository = $this->getRepository(UserRepository::class);
		/** @var User $user */
		$user  = $userRepository->findOneBy(['active' => false]);
		$admin = $this->getOneAdmin();
		$route = sprintf('/admin/users/%s/activate', $user->getUuid());
		$this->client->loginUser($admin);
		$this->client->request(Request::METHOD_GET, $route);
		/** @var User $user */
		$user = $userRepository->findOneBy(['uuid' => $user->getUuid()]);
		static::assertTrue($user->getActive());
	}
}