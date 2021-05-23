<?php

declare(strict_types = 1);

namespace App\Controller\User\Profile;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PrivateProfileController extends AbstractController {
	/**
	 * @Route("/profile", name="profile-show-self", methods={"GET"})
	 */
	public function __invoke(): Response {
		if ($this->isGranted(User::ROLE_TEST_USER)) {
			return $this->render('user/profile/show-self.html.twig', ['user' => $this->getUser()]);
		}
		if ($this->isGranted(User::ROLE_USER)) {
			return $this->render('user/profile/show-self.html.twig', ['user' => $this->getUser()]);
		}
		if (! $this->isGranted(User::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
}