<?php

declare(strict_types = 1);

namespace App\Event\Creators;

use App\Entity\Contracts\AuthorableContract;
use Symfony\Contracts\EventDispatcher\Event;

class AuthorableCreateEvent extends Event {
	public function __construct(public AuthorableContract $entity) { }
}