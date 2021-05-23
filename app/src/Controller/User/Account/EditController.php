<?php

declare(strict_types = 1);

namespace App\Controller\User\Account;

use App\Controller\Concerns\FlashFormErrors;

use App\Entity\User;
use App\Event\Security\EmailModifiedEvent;
use App\Event\Security\PasswordHashEvent;
use App\Form\User\Account\EditType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EditController extends AbstractController {
	use FlashFormErrors;
	
	public function __construct(private EntityManagerInterface $em, private EventDispatcherInterface $ed) { }
	
	/**
	 * @Route("/account/edit", name="account-edit", methods={"GET", "POST"})
	 */
	public function __invoke(Request $request): Response {
		if (! $this->isGranted(User::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		/** @var User $user */
		$user     = $this->getUser();
		$oldEmail = $user->getEmail();
		if (! $this->isGranted(User::ROLE_EDIT_ACCOUNT, $user)) {
			throw $this->createNotFoundException();
		}
		$form = $this->createForm(EditType::class, $user);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if ($user->getPlainPassword() !== null) {
				$this->ed->dispatch(new PasswordHashEvent($user));
			}
			if ($oldEmail !== $user->getEmail()) {
				$this->ed->dispatch(new EmailModifiedEvent((string) $oldEmail, $user));
			}
			$this->em->flush();
			return $this->redirectToRoute('profile-show-self');
		}
		$this->flashFormErrors($form);
		return $this->render('user/account/edit.html.twig', ['accountEditForm' => $form->createView()]);
	}
}