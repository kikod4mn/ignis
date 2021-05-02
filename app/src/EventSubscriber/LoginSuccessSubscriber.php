<?php

declare(strict_types = 1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\TimeCreator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSuccessSubscriber implements EventSubscriberInterface {
	public function __construct(private EntityManagerInterface $em) { }
	
	/**
	 * @return array<string, array<int, string<int, int>>>
	 */
	public static function getSubscribedEvents(): array {
		return [LoginSuccessEvent::class => ['onLoginSuccess', 9999]];
	}
	
	public function onLoginSuccess(LoginSuccessEvent $event): void {
		$user = $event->getUser();
		if (! $user instanceof User) {
			return;
		}
		$user
			->setLastLoginAt(TimeCreator::now())
			->setLastLoginFromIp($event->getRequest()->getClientIp())
			->setLastLoginFromBrowser($event->getRequest()->headers->get('User-Agent'))
		;
		$this->em->flush();
	}
}