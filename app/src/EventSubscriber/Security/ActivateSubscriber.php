<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Security;

use App\Event\Security\ActivateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActivateSubscriber implements EventSubscriberInterface {
	public static function getSubscribedEvents(): array {
		return [ActivateEvent::class => ['notify']];
	}
	
	public function notify(ActivateEvent $event): void {
		// todo send user an email notifying them of an activated account
	}
}