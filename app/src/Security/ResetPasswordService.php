<?php

declare(strict_types = 1);

namespace App\Security;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Repository\ResetPasswordRequestRepository;
use App\Service\TokenGenerator;
use Carbon\Carbon;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

final class ResetPasswordService {
	public function __construct(private string $signingKey, private TokenGenerator $tokenGenerator, private ResetPasswordRequestRepository $resetPasswordRepository) { }
	
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
		$resetRequest  = $this->findResetRequest($fullToken);
		$user          = $resetRequest->getUser();
		$verifierToken = json_encode([substr($fullToken), $user->getId(), $resetRequest->getExpiresAt()], JSON_THROW_ON_ERROR);
		dd(hash_equals($resetRequest->getHashedToken(), $this->getHashedToken($verifierToken)));
		if (! hash_equals($resetRequest->getHashedToken(), $this->getHashedToken($verifierToken))) {
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
		$request = $this->resetPasswordRepository->findOneBy(['selector' => substr($token, 0, 20)]);
		if ($request === null) {
			throw new CustomUserMessageAccountStatusException('Either no account found for the token or this token is expired. Request a new one.');
		}
		return $request;
	}
}