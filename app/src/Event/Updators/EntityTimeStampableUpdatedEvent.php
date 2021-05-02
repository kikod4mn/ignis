<?php

declare(strict_types = 1);

namespace App\Event\Updators;

use App\Entity\Contracts\TimeStampableContract;
use Symfony\Contracts\EventDispatcher\Event;

class EntityTimeStampableUpdatedEvent extends Event {
	public function __construct(public TimeStampableContract $entity) { }
}