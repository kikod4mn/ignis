<?php

declare(strict_types = 1);

namespace App\Controller\Security\Logout;

use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class LogoutController {
	/**
	 * @Route("/logout", name="security-logout")
	 */
	public function __invoke(): void { }
}