<?php

declare(strict_types = 1);

namespace App\Security;

use App\Entity\User;
use App\Security\Exception\AccountDisabledException;
use App\Security\Exception\AccountNotActiveException;
use App\Security\Exception\EmailNotConfirmedException;
use DateTimeInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface {
	public function checkPreAuth(UserInterface $user): void {
		if (! $user instanceof User) {
			return;
		}
		if (! $user->getEmailConfirmedAt() instanceof DateTimeInterface && $user->getEmailConfirmToken() !== null) {
			$e = new EmailNotConfirmedException();
			$e->setUser($user);
			throw $e;
		}
		if (! $user->getActive()) {
			$e = new AccountNotActiveException();
			$e->setUser($user);
			throw $e;
		}
	}
	
	public function checkPostAuth(UserInterface $user): void {
		if (! $user instanceof User) {
			return;
		}
		if ($user->getDisabled()) {
			$e = new AccountDisabledException();
			$e->setUser($user);
			return;
		}
	}
}