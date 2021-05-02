<?php

declare(strict_types = 1);

namespace App\Event\Creators;

use App\Entity\Contracts\EntityAuthorableContract;
use Symfony\Contracts\EventDispatcher\Event;

class EntityAuthorableCreatedEvent extends Event {
	public function __construct(public EntityAuthorableContract $entity) { }
}