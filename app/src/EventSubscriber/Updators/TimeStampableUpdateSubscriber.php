<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Updators;

use App\Event\Updators\TimeStampableUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TimeStampableUpdateSubscriber implements EventSubscriberInterface {
	
	/**
	 * @return array<string, array<int, string>>
	 */
	public static function getSubscribedEvents(): array {
		return [TimeStampableUpdateEvent::class => ['updated']];
	}
	
	public function updated(TimeStampableUpdateEvent $event): void {
		$entity = $event->entity;
		if ($entity->getUpdatedAt() === null) {
			$entity->setUpdatedTimestamps();
		}
	}
}