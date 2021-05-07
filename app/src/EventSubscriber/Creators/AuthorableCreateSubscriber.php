<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Creators;

use App\Entity\User;
use App\Event\Creators\AuthorableCreateEvent;
use LogicException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

class AuthorableCreateSubscriber implements EventSubscriberInterface {
	public function __construct(private Security $security) { }
	
	/**
	 * @return array<string, array<int, string>>
	 */
	public static function getSubscribedEvents(): array {
		return [AuthorableCreateEvent::class => ['created']];
	}
	
	public function created(AuthorableCreateEvent $event): void {
		$user = $this->security->getUser();
		if (! $user instanceof User) {
			throw new LogicException('Nobody logged in!');
		}
		$event->entity->setAuthor($user);
	}
}