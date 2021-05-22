<?php

declare(strict_types = 1);

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class EmailNotConfirmedException extends CustomUserMessageAccountStatusException {
	public function __construct() {
		parent::__construct('Your email is not yet confirmed. Please check your email for further instructions.');
	}
}