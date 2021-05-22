<?php

declare(strict_types = 1);

namespace App\Event\User\Account;

use App\Entity\User;

class RegisterEvent {
	public function __construct(public User $user) { }
}