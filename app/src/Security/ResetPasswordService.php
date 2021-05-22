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
	
	public function createResetRequest(User $user, Request $request): ResetPasswordRequest {
		$expiresAt = Carbon::now()->addHours(3);
		$selector  = $this->tokenGenerator->alphanumericToken(20);
		$verifier  = $this->tokenGenerator->alphanumericToken(20);
		$encoded   = json_encode([$verifier, $user->getId(), $expiresAt]);
		return new ResetPasswordRequest(
			$user,
			$expiresAt,
			$selector,
			$this->getHashedToken($encoded),
			(string) $request->headers->get('User-Agent'),
			(string) $request->getClientIp()
		);
	}
	
	public function validateResetRequest(string $token): User {
		$resetRequest        = $this->findResetRequest($token);
		$user                = $resetRequest->getUser();
		$hashedVerifierToken = json_encode([mb_substr($token, 0, 20), $user->getId(), $resetRequest->getExpiresAt()]);
		if (hash_equals($resetRequest->getHashedToken(), $this->getHashedToken($hashedVerifierToken))) {
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