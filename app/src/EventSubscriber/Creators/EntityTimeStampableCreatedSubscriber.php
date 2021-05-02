<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Creators;

use App\Event\Creators\EntityTimeStampableCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntityTimeStampableCreatedSubscriber implements EventSubscriberInterface {
	/**
	 * @return array<string, array<int, string>>
	 */
	public static function getSubscribedEvents(): array {
		return [EntityTimeStampableCreatedEvent::class => ['created']];
	}
	
	public function created(EntityTimeStampableCreatedEvent $event): void {
		$entity = $event->entity;
		if ($entity->getCreatedAt() === null) {
			$entity->setCreationTimestamps();
		}
	}
}