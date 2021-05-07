<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Security;

use App\Event\Security\UserEmailModifiedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserEmailModifiedSubscriber implements EventSubscriberInterface {
	public static function getSubscribedEvents(): array {
		return [
			UserEmailModifiedEvent::class => [
				['modify', 9999],
				['notify', 9998],
			],
		];
	}
	
	public function modify(UserEmailModifiedEvent $event): void {
		$user = $event->user;
		$user->addOldEmail($event->oldEmail);
	}
	
	public function notify(UserEmailModifiedEvent $event): void {
		// todo notify user of email change to both emails
	}
}