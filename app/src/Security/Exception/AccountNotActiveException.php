<?php

declare(strict_types = 1);

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class AccountNotActiveException extends CustomUserMessageAccountStatusException {
	public function __construct() {
		parent::__construct('Your account is not active. Please check your email for further instructions.');
	}
}