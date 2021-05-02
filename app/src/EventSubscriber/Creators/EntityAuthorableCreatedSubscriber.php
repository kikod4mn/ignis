<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Creators;

use App\Entity\User;
use App\Event\Creators\EntityAuthorableCreatedEvent;
use LogicException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

class EntityAuthorableCreatedSubscriber implements EventSubscriberInterface {
	public function __construct(private Security $security) { }
	
	/**
	 * @return array<string, array<int, string>>
	 */
	public static function getSubscribedEvents(): array {
		return [EntityAuthorableCreatedEvent::class => ['created']];
	}
	
	public function created(EntityAuthorableCreatedEvent $event): void {
		$user = $this->security->getUser();
		if (! $user instanceof User) {
			throw new LogicException('Nobody logged in!');
		}
		$event->entity->setAuthor($user);
	}
}