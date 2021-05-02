<?php

declare(strict_types = 1);

namespace App\Controller\Security\Login;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use function dd;

/**
 * @codeCoverageIgnore
 */
class LoginController extends AbstractController {
	/**
	 * @Route("/login", name="security-login")
	 */
	public function __invoke(AuthenticationUtils $utils): Response {
		if (! $this->isGranted('IS_ANONYMOUS')) {
			return $this->redirectToRoute('home');
		}
		return $this->render(
			'security/login/login.html.twig',
			[
				'error'        => $utils->getLastAuthenticationError(),
				'lastUsername' => $utils->getLastUsername(),
			]
		);
	}
}