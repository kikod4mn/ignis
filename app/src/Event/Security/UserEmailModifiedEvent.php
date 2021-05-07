<?php

declare(strict_types = 1);

namespace App\Event\Security;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserEmailModifiedEvent extends Event {
	public function __construct(public string $oldEmail, public User $user) { }
}