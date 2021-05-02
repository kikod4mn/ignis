<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Updators;

use App\Event\Updators\EntityTimeStampableUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntityTimeStampableUpdatedSubscriber implements EventSubscriberInterface {
	
	/**
	 * @return array<string, array<int, string>>
	 */
	public static function getSubscribedEvents(): array {
		return [EntityTimeStampableUpdatedEvent::class => ['updated']];
	}
	
	public function updated(EntityTimeStampableUpdatedEvent $event): void {
		$entity = $event->entity;
		if ($entity->getUpdatedAt() === null) {
			$entity->setUpdatedTimestamps();
		}
	}
}