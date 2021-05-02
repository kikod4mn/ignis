<?php

declare(strict_types = 1);

namespace App\Event\Creators;

use App\Entity\Contracts\EntityHistoryContract;
use Symfony\Contracts\EventDispatcher\Event;

class CreateEntityHistoryEvent extends Event {
	public function __construct(public EntityHistoryContract $entity, public string $field, public string $value) { }
}