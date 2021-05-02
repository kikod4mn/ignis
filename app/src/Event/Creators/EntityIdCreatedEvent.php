<?php

declare(strict_types = 1);

namespace App\Event\Creators;

use App\Entity\Contracts\EntityIdContract;
use Symfony\Contracts\EventDispatcher\Event;

class EntityIdCreatedEvent extends Event {
	public function __construct(public EntityIdContract $entity) { }
}