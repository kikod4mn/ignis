<?php

declare(strict_types = 1);

namespace App\Controller\Account;

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
	 * @return array<string, object|null>
	 */
	public function __invoke(User $user): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->render('account/show-self.html.twig', ['user' => $this->getUser()]);
		}
		if ($this->isGranted(Role::ROLE_USER)) {
			return $this->render('account/show-public.html.twig', ['user' => $user]);
		}
		throw $this->createNotFoundException();
	}
}