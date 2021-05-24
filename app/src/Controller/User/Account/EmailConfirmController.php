<?php

declare(strict_types = 1);

namespace App\Controller\User\Account;

use App\Security\ConfirmEmailService;
use App\Service\Contracts\Flashes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmailConfirmController extends AbstractController {
	public function __construct(private ConfirmEmailService $emailConfirmService) { }
	
	/**
	 * @Route("/credentials/confirm/email/{token}", name="credentials-email-confirmation", methods={"GET"})
	 * @IsGranted("IS_ANONYMOUS")
	 */
	public function __invoke(string $token): Response {
		$this->emailConfirmService->validateResetRequest($token);
		$this->addFlash(Flashes::INFO_MESSAGE, 'Awesome! Your email has been confirmed! After an admin activates your account, you will have full API access!');
		return $this->render('home/index.html.twig');
	}
}