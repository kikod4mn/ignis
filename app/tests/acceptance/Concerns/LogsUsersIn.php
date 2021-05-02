<?php

declare(strict_types = 1);

namespace App\Tests\acceptance\Concerns;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

trait LogsUsersIn {
	protected function loginUser(User $user): void {
		static::$client->request(Request::METHOD_GET, '/login');
		static::$client->submit(
			static::$client->getCrawler()
						   ->selectButton('Sign in')
						   ->form(
							   [
								   'security_login[_email]'    => $user->getEmail(),
								   'security_login[_password]' => 'secret',
							   ]
						   )
		);
	}
}