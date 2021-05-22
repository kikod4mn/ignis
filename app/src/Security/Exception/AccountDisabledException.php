<?php

declare(strict_types = 1);

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class AccountDisabledException extends CustomUserMessageAccountStatusException {
	public function __construct() {
		parent::__construct('Your account is disabled. You should have received an email stating the reason.');
	}
}