<?php

declare(strict_types = 1);

namespace App\Event\Creators;

use App\Entity\Contracts\BackupContract;
use Symfony\Contracts\EventDispatcher\Event;

class BackupCreateEvent extends Event {
	public function __construct(public BackupContract $entity) { }
}