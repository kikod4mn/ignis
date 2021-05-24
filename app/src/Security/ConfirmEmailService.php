<?php

declare(strict_types = 1);

namespace App\Security;

use App\Entity\ConfirmEmailRequest;
use App\Entity\User;
use App\Repository\ConfirmEmailRequestRepository;
use App\Service\TokenGenerator;
use Carbon\Carbon;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

final class ConfirmEmailService {
	public const SELECTOR_LENGTH = 20;
	
	public function __construct(private string $signingKey, private TokenGenerator $tokenGenerator, private ConfirmEmailRequestRepository $confirmEmailRequestRepository) { }
	
	public function createConfirmRequest(User $user, string $fromIp, string $fromBrowser): ConfirmEmailRequest {
		$expiresAt = Carbon::now()->addHours(3);
		$selector  = $this->tokenGenerator->alphanumericToken(self::SELECTOR_LENGTH);
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
		$resetRequest  = $this->findConfirmRequest($token);
		$user          = $resetRequest->getUser();
		$verifierToken = json_encode([mb_substr($token, self::SELECTOR_LENGTH), $user->getId(), $resetRequest->getExpiresAt()], JSON_THROW_ON_ERROR);
		if (! hash_equals($resetRequest->getHashedToken(), $this->getHashedToken($verifierToken))) {
			// todo token is wrong!!!
			dd('throw', $token, $resetRequest->getHashedToken(), $this->getHashedToken($verifierToken));
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
		$request = $this->confirmEmailRequestRepository->findOneBy(['selector' => mb_substr($token, 0, self::SELECTOR_LENGTH)]);
		if ($request === null) {
			throw new CustomUserMessageAccountStatusException('Either no account found for the token or this token is expired. Request a new one.');
		}
		return $request;
	}
}