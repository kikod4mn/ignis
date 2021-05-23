<?php

declare(strict_types = 1);

namespace App\Controller\Admin\User;


use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShowController extends AbstractController {
	/**
	 * @Route("/admin/users/{user_uuid}/show", name="profile-show-admin", methods={"GET"})
	 * @ParamConverter("user", class="App\Entity\User", options={"mapping": {"user_uuid":"uuid"}})
	 */
	public function __invoke(User $user): Response {
		if ($this->isGranted(User::ROLE_TEST_USER)) {
			return $this->showcaseShow($user);
		}
		if ($this->isGranted(User::ROLE_ADMIN)) {
			return $this->showcaseShow($user);
		}
		throw $this->createNotFoundException();
	}
	
	private function showcaseShow(User $user): Response {
		return $this->render('admin/users/show.html.twig', ['user' => $user]);
	}
}