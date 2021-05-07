<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Creators;

use App\Event\Creators\IdCreateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class IdCreateSubscriber implements EventSubscriberInterface {
	/**
	 * @return array<string, array<int, string>>
	 */
	public static function getSubscribedEvents(): array {
		return [IdCreateEvent::class => ['created']];
	}
	
	public function created(IdCreateEvent $event): void {
		$event->entity->generateUuid();
	}
}