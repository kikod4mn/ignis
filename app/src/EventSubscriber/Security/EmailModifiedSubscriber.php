<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Security;

use App\Event\Security\EmailModifiedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmailModifiedSubscriber implements EventSubscriberInterface {
	public static function getSubscribedEvents(): array {
		return [
			EmailModifiedEvent::class => [
				['modify', 9999],
				['notify', 9998],
			],
		];
	}
	
	public function modify(EmailModifiedEvent $event): void {
		$user = $event->user;
		$user->addOldEmail($event->oldEmail);
	}
	
	public function notify(EmailModifiedEvent $event): void {
		// todo notify user of email change to both emails
	}
}