<?php

declare(strict_types = 1);

namespace App\Event\Creators;

use App\Entity\Contracts\TimeStampableContract;
use Symfony\Contracts\EventDispatcher\Event;

class EntityTimeStampableCreatedEvent extends Event {
	public function __construct(public TimeStampableContract $entity) { }
}