<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Security;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

class AuthenticationFailureSubscriber implements EventSubscriberInterface {
	public static function getSubscribedEvents(): array {
		return [KernelEvents::EXCEPTION => ['failure', 99999999999999999]];
	}
	
	public function failure(ExceptionEvent $event): void {
//		dd($event);
	}
}