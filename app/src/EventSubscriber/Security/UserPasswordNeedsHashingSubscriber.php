<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Security;

use App\Event\Security\UserPasswordNeedsHashingEvent;
use LogicException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use function dump;
use function is_string;

class UserPasswordNeedsHashingSubscriber implements EventSubscriberInterface {
	public function __construct(private UserPasswordEncoderInterface $encoder) { }
	
	/**
	 * @return array<string, array<int, string>>
	 */
	public static function getSubscribedEvents(): array {
		return [UserPasswordNeedsHashingEvent::class => ['hashIt']];
	}
	
	public function hashIt(UserPasswordNeedsHashingEvent $event): void {
		$user = $event->user;
		if ($user->getPlainPassword() === null) {
			throw new LogicException('User must have a plain password in order to save.');
		}
		$oldPwdHash = is_string($user->getPassword()) ? $user->getPassword() : null;
		if ($oldPwdHash) {
			$user->addOldPasswordHash($oldPwdHash);
		}
		$user->setPassword(
			$this->encoder->encodePassword($user, $user->getPlainPassword())
		);
	}
}