<?php

declare(strict_types = 1);

namespace App\Controller\Concerns;

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @codeCoverageIgnore
 */
trait FlashHome {
	protected function flashHome(string $type, string $message): RedirectResponse {
		$this->addFlash($type, $message);
		return $this->redirectToRoute('home');
	}
}