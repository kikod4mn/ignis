<?php

declare(strict_types = 1);

namespace App\Security;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Service\TokenGenerator;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;

final class ResetPasswordService {
	public function __construct(private string $signingKey, private TokenGenerator $tokenGenerator) { }
	
	public function createResetRequest(User $user, string $fromIp, string $fromBrowser): ResetPasswordRequest {
		$expiresAt = Carbon::now()->addHours(3);
		$selector  = $this->tokenGenerator->alphanumericToken(20);
		$verifier  = $this->tokenGenerator->alphanumericToken(20);
		$encoded   = json_encode([$verifier, $user->getId(), $expiresAt], JSON_THROW_ON_ERROR);
		return new ResetPasswordRequest(
			$user,
			$expiresAt,
			$selector,
			$this->getHashedToken($encoded),
			$fromIp,
			$fromBrowser
		);
	}
	
	public function validateResetRequest(string $fullToken): User {
		$resetRequest        = $this->findResetRequest($fullToken);
		$user                = $resetRequest->getUser();
		$hashedVerifierToken = json_encode([mb_substr($fullToken, 0, 20), $user->getId(), $resetRequest->getExpiresAt()], JSON_THROW_ON_ERROR);
		if (! hash_equals($resetRequest->getHashedToken(), $this->getHashedToken($hashedVerifierToken))) {
			// todo token is wrong!!!
			dd('throw');
		}
		return $user;
	}
	
	private function getHashedToken(string $encoded): string {
		return base64_encode(hash_hmac('sha256', $encoded, $this->signingKey, true));
	}
	
	private function sendPasswordResetEmail(User $user): void {
//		$this->mailer->htmlMessage(
//			(string) $user->getEmail(),
//			'Password reset token requested.',
//			'base/mail-templates/forgot-password.html.twig',
//			['user' => $user]
//		);
	}
	
	private function findResetRequest(string $token): ResetPasswordRequest {
	}
}