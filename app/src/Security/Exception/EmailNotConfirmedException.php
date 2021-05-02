<?php

declare(strict_types = 1);

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class EmailNotConfirmedException extends AccountStatusException {
	public function __construct() {
		parent::__construct('Your email is not yet confirmed. Please check your email for further instructions.');
	}
}