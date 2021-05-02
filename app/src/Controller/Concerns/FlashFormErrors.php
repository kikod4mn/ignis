<?php

declare(strict_types = 1);

namespace App\Controller\Concerns;

use App\Service\Contracts\Flashes;
use Symfony\Component\Form\FormInterface;

/**
 * @codeCoverageIgnore
 */
trait FlashFormErrors {
	protected function flashFormErrors(FormInterface $form): void {
		if ($form->isSubmitted() && ! $form->isValid()) {
			foreach ($form->getErrors(true, true) as $error) {
				$this->addFlash(Flashes::DANGER_MESSAGE, $error->getMessage());
			}
		}
	}
}