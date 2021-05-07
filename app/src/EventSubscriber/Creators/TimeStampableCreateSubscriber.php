<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Creators;

use App\Event\Creators\TimeStampableCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TimeStampableCreateSubscriber implements EventSubscriberInterface {
	/**
	 * @return array<string, array<int, string>>
	 */
	public static function getSubscribedEvents(): array {
		return [TimeStampableCreatedEvent::class => ['created']];
	}
	
	public function created(TimeStampableCreatedEvent $event): void {
		$entity = $event->entity;
		if ($entity->getCreatedAt() === null) {
			$entity->setCreationTimestamps();
		}
	}
}