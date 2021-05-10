<?php

declare(strict_types = 1);

namespace App\Controller\User\Profile;

use App\Entity\Role;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicProfileController extends AbstractController {
	/**
	 * @Route("/{user_uuid}/profile", name="profile-show-public", methods={"GET"})
	 * @ParamConverter("user", class="App\Entity\User", options={"mapping": {"user_uuid": "uuid"}})
	 */
	public function __invoke(User $user): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->render('user/profile/show-self.html.twig', ['user' => $this->getUser()]);
		}
		if ($this->isGranted(Role::ROLE_USER)) {
			return $this->render('user/profile/show-public.html.twig', ['user' => $user]);
		}
		if (! $this->isGranted(Role::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
}