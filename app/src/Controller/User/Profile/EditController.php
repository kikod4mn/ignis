<?php

declare(strict_types = 1);

namespace App\Controller\User\Profile;

use App\Controller\Concerns\FlashFormErrors;
use App\Entity\Role;
use App\Entity\User;
use App\Event\Creators\VersionCreateEvent;
use App\Form\User\Profile\EditType;
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
	 * @Route("/profile/edit", name="profile-edit", methods={"GET", "POST"})
	 */
	public function __invoke(Request $request): Response {
		if (! $this->isGranted(Role::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		/** @var User $user */
		$user = $this->getUser();
		if (! $this->isGranted(Role::ROLE_EDIT_PROFILE, $user)) {
			throw $this->createNotFoundException();
		}
		$oldName = $user->getName();
		$form    = $this->createForm(EditType::class, $user);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if ($oldName !== $user->getName()) {
				$this->ed->dispatch(new VersionCreateEvent($user, 'name', (string) $oldName));
			}
			$this->em->flush();
			return $this->redirectToRoute('profile-show-self');
		}
		$this->flashFormErrors($form);
		return $this->render('user/profile/edit.html.twig', ['profileEditForm' => $form->createView()]);
	}
}