<?php

declare(strict_types = 1);

namespace App\Controller\User\Account;

use App\Entity\Role;
use App\Entity\User;
use App\Event\Security\EmailModifiedEvent;
use App\Event\Security\PasswordHashEvent;
use App\Form\User\AccountEditType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AccountEditController extends AbstractController {
	public function __construct(private EntityManagerInterface $em, private EventDispatcherInterface $ed) { }
	
	/**
	 * @Route("/account/edit", name="account-edit", methods={"GET", "POST"})
	 */
	public function __invoke(Request $request): Response {
		if (! $this->isGranted(Role::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		/** @var User $user */
		$user     = $this->getUser();
		$oldEmail = $user->getEmail();
		if (! $this->isGranted(Role::ROLE_EDIT_ACCOUNT, $user)) {
			throw $this->createNotFoundException();
		}
		$form = $this->createForm(AccountEditType::class, $user);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if ($user->getPlainPassword()) {
				$this->ed->dispatch(new PasswordHashEvent($user));
			}
			if ($oldEmail !== $user->getEmail()) {
				$this->ed->dispatch(new EmailModifiedEvent($oldEmail, $user));
			}
			$this->em->flush();
			return $this->redirectToRoute('profile-show-self');
		}
		return $this->render('user/account/edit.html.twig', ['accountEditForm' => $form->createView()]);
	}
}