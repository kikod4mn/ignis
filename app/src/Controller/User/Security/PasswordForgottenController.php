<?php

declare(strict_types = 1);

namespace App\Controller\User\Security;

use App\Controller\Concerns\FlashFormErrors;
use App\Form\User\Security\PasswordForgottenType;
use App\Repository\UserRepository;
use App\Service\Contracts\Flashes;
use App\Service\Mailer;
use App\Service\TimeCreator;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PasswordForgottenController extends AbstractController {
	use FlashFormErrors;
	
	public function __construct(private UserRepository $userRepository, private TokenGenerator $token, private EntityManagerInterface $em, private Mailer $mailer) { }
	
	/**
	 * @Route("/credentials/request/forgotten-password", name="credentials-forgot-password", methods={"GET", "POST"})
	 */
	public function __invoke(Request $request): Response {
		if (! $this->isGranted('IS_ANONYMOUS')) {
			throw $this->createNotFoundException();
		}
		$form = $this->createForm(PasswordForgottenType::class);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$user = $this->userRepository->findOneBy(['email' => $form->get('_email')->getData()]);
			if ($user === null) {
				return $this->returnSuccess();
			}
			$user
				->setPasswordResetToken($this->token->alphanumericToken(64))
				->setPasswordResetTokenRequestedAt(TimeCreator::now())
				->setPasswordResetTokenRequestedFromBrowser($request->headers->get('User-Agent'))
				->setPasswordResetTokenRequestedFromIp($request->getClientIp())
			;
			$this->em->flush();
			$this->mailer->htmlMessage(
				(string) $user->getEmail(),
				'Password reset token requested.',
				'base/mail-templates/forgot-password.html.twig',
				['user' => $user]
			);
			return $this->returnSuccess();
		}
		$this->flashFormErrors($form);
		return $this->render('user/security/login/password-forgotten.html.twig', ['form' => $form]);
	}
	
	private function returnSuccess(): RedirectResponse {
		$this->addFlash(
			Flashes::SUCCESS_MESSAGE,
			'You have successfully requested a password reset token. Please check your email and follow the instructions. It may take some time for the email to arrive.'
		);
		
		return $this->redirectToRoute('home');
	}
}