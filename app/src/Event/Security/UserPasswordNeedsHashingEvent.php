<?php

declare(strict_types = 1);

namespace App\Event\Security;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserPasswordNeedsHashingEvent extends Event {
	public function __construct(public User $user) { }
}