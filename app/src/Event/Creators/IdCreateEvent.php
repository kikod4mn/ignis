<?php

declare(strict_types = 1);

namespace App\Event\Creators;

use App\Entity\Contracts\IdContract;
use Symfony\Contracts\EventDispatcher\Event;

class IdCreateEvent extends Event {
	public function __construct(public IdContract $entity) { }
}