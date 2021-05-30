<?php

declare(strict_types = 1);

namespace App\Security;

use App\Entity\User;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final class EmailVerifier {
	public function __construct(private VerifyEmailHelperInterface $helper, private MailerInterface $mailer, private EntityManagerInterface $em) { }
	
	public function sendEmailConfirmation(string $verifyEmailRouteName, User $user, TemplatedEmail $email): void {
		$signatureComponents             = $this->helper->generateSignature(
			$verifyEmailRouteName,
			(string) $user->getId(),
			(string) $user->getEmail(),
			['id' => $user->getId()]
		);
		$context                         = $email->getContext();
		$context['signedUrl']            = $signatureComponents->getSignedUrl();
		$context['expiresAtMessageKey']  = $signatureComponents->getExpirationMessageKey();
		$context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();
		$email->context($context);
		$this->mailer->send($email);
	}
	
	public function handleEmailConfirmation(Request $request, User $user): void {
		$this->helper->validateEmailConfirmation($request->getUri(), (string) $user->getId(), (string) $user->getEmail());
		$user->setEmailConfirmed(true);
		$user->setEmailConfirmedAt(Carbon::now());
		$this->em->flush();
	}
}
