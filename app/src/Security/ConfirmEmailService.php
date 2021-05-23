<?php

declare(strict_types = 1);

namespace App\Security;

use App\Entity\ConfirmEmailRequest;
use App\Entity\User;
use App\Repository\ConfirmEmailRequestRepository;
use App\Repository\UserRepository;
use App\Service\TimeCreator;
use App\Service\TokenGenerator;
use Carbon\Carbon;

final class ConfirmEmailService {
	public function __construct(
		private string $signingKey, private TokenGenerator $tokenGenerator,
		private ConfirmEmailRequestRepository $confirmEmailRequestRepository
	) {
	}
	
	public function createConfirmRequest(User $user, string $fromIp, string $fromBrowser): ConfirmEmailRequest {
		$expiresAt = Carbon::now()->addHours(3);
		$selector  = $this->tokenGenerator->alphanumericToken(20);
		$verifier  = $this->tokenGenerator->alphanumericToken(20);
		$encoded   = json_encode([$verifier, $user->getId(), $expiresAt], JSON_THROW_ON_ERROR);
		return new ConfirmEmailRequest(
			$user,
			$expiresAt,
			$selector,
			$this->getHashedToken($encoded),
			$fromIp,
			$fromBrowser
		);
	}
	
	public function validateResetRequest(string $token): User {
		$resetRequest        = $this->findConfirmRequest($token);
		$user                = $resetRequest->getUser();
		$hashedVerifierToken = json_encode([mb_substr($token, 0, 20), $user->getId(), $resetRequest->getExpiresAt()], JSON_THROW_ON_ERROR);
		if (! hash_equals($resetRequest->getHashedToken(), $this->getHashedToken($hashedVerifierToken))) {
			// todo token is wrong!!!
			dd('throw');
		}
		return $user;
	}
	
	public function sendConfirmationEmail(User $user): void {
		// todo send confirmation link email
	}
	
	private function getHashedToken(string $encoded): string {
		return base64_encode(hash_hmac('sha256', $encoded, $this->signingKey, true));
	}
	
	private function findConfirmRequest(string $token): ConfirmEmailRequest {
	
	}
}