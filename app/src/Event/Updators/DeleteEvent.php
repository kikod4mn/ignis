<?php

declare(strict_types = 1);

namespace App\Event\Updators;

use Symfony\Contracts\EventDispatcher\Event;

class DeleteEvent extends Event {
	public function __construct(public object $entity) { }
}