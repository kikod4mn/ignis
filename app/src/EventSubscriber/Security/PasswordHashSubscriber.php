<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Security;

use App\Event\Security\PasswordHashEvent;
use LogicException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use function is_string;

class PasswordHashSubscriber implements EventSubscriberInterface {
	public function __construct(private UserPasswordEncoderInterface $encoder) { }
	
	public static function getSubscribedEvents(): array {
		return [
			PasswordHashEvent::class => [
				['hashIt', 9999],
				['checkOldHashes', 9998],
			],
		];
	}
	
	public function hashIt(PasswordHashEvent $event): void {
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
	
	public function checkOldHashes(PasswordHashEvent $event): void {
		//todo validate old password hashes so the new password is not one used previously
	}
}