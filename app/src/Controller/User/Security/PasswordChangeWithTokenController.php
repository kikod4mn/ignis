<?php

declare(strict_types = 1);

namespace App\Controller\User\Security;

use App\Controller\Concerns\FlashFormErrors;
use App\Controller\Concerns\FlashHome;
use App\Event\Security\PasswordHashEvent;
use App\Form\User\Security\PasswordChangeWithTokenType;
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
			throw $this->createNotFoundException();
		}
		if ($user === null) {
			$this->addFlash(Flashes::DANGER_MESSAGE, 'No user found with the token. Please verify the link is correct.');
			return $this->redirectToRoute('home');
		}
		$form = $this->createForm(PasswordChangeWithTokenType::class);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$user->setPasswordResetToken(null);
			$user->setPlainPassword($form->get('_password')->getData());
			$this->ed->dispatch(new PasswordHashEvent($user));
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
			'user/security/login/password-change-with-token.html.twig',
			[
				'passwordToken' => $passwordToken,
			]
		);
	}
}