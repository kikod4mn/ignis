<?php

declare(strict_types = 1);

namespace App\Event\Creators;

use App\Entity\Contracts\EntityBackupContract;
use Symfony\Contracts\EventDispatcher\Event;

class CreateEntityBackupEvent extends Event {
	public function __construct(public EntityBackupContract $entity) { }
}