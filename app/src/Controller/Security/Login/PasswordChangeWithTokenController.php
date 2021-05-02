<?php

declare(strict_types = 1);

namespace App\Controller\Security\Login;

use App\Controller\Concerns\FlashFormErrors;
use App\Controller\Concerns\FlashHome;
use App\Event\Security\UserPasswordNeedsHashingEvent;
use App\Form\User\PasswordChangeWithTokenType;
use App\Repository\UserRepository;
use App\Service\Contracts\Flashes;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PasswordChangeWithTokenController extends AbstractController {
	use FlashFormErrors;
	use FlashHome;
	
	public function __construct(
		private Mailer $mailer, private UserRepository $userRepository,
		private EntityManagerInterface $em, private EventDispatcherInterface $ed
	) {
	}
	
	/**
	 * @Route("/credentials/change-password/{passwordToken}", name="credentials-password-change-with-token", methods={"GET", "POST"})
	 */
	public function __invoke(Request $request, string $passwordToken): Response {
		$user = $this->userRepository->findOneBy(['passwordResetToken' => $passwordToken]);
		if (! $this->isGranted('IS_ANONYMOUS')) {
			return $this->flashHome(Flashes::DANGER_MESSAGE, 'You are logged in and cannot access this functionality.');
		}
		if (! $user) {
			$this->addFlash(Flashes::DANGER_MESSAGE, 'No user found with the token. Please verify the link is correct.');
			return $this->redirectToRoute('home');
		}
		$form = $this->createForm(PasswordChangeWithTokenType::class);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$user->setPasswordResetToken(null);
			$user->setPlainPassword($form->get('_password')->getData());
			$this->ed->dispatch(new UserPasswordNeedsHashingEvent($user));
			$this->em->flush();
			$this->mailer->htmlMessage(
				(string) $user->getEmail(),
				'Password successfully changed',
				'base/mail-templates/password-change-with-token-success.html.twig',
				['user' => $user]
			);
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Password successfully changed. You must now login with the new password.');
			return $this->redirectToRoute('home');
		}
		$this->flashFormErrors($form);
		return $this->render(
			'security/login/password-change-with-token.html.twig',
			[
				'passwordToken' => $passwordToken,
			]
		);
	}
}