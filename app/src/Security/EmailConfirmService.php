<?php

declare(strict_types = 1);

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\TimeCreator;
use App\Service\TokenGenerator;

final class EmailConfirmService {
	public function __construct(private UserRepository $userRepository) { }
	
	public function setTokenAndSendEmail(User $user): void {
		$user->setEmailConfirmToken((new TokenGenerator())->alphanumericToken(64));
		$this->sendConfirmationEmail($user);
	}
	
	public function verifyAndConfirm(string $token): bool {
		$user = $this->userRepository->findOneBy(['emailConfirmToken' => $token]);
		if ($user === null || $user->getEmailConfirmationTokenExpiresAt()?->getTimestamp() < time()) {
			return false;
		}
		$user->setEmailConfirmedAt(TimeCreator::now())->setEmailConfirmToken(null);
		return true;
	}
	
	public function sendConfirmationEmail(User $user): void {
		// todo send confirmation link email
	}
}