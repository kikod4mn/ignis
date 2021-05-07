<?php

declare(strict_types = 1);

namespace App\Event\Creators;

use App\Entity\Contracts\VersionableContract;
use Symfony\Contracts\EventDispatcher\Event;

class VersionCreateEvent extends Event {
	public function __construct(public VersionableContract $entity, public string $field, public string $value) { }
}