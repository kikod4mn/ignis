<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Creators;

use App\Event\Creators\EntityIdCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntityIdCreatedSubscriber implements EventSubscriberInterface {
	/**
	 * @return array<string, array<int, string>>
	 */
	public static function getSubscribedEvents(): array {
		return [EntityIdCreatedEvent::class => ['created']];
	}
	
	public function created(EntityIdCreatedEvent $event): void {
		$event->entity->generateUuid();
	}
}