<?php

declare(strict_types = 1);

namespace App\Controller\User\Register;

use App\Controller\Concerns\FlashFormErrors;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterHelpController extends AbstractController {
	use FlashFormErrors;
	
	public function __construct(private UserRepository $userRepository) { }
	
	/**
	 * @Route("/register/check-email", name="security-check-email-exists", methods={"POST"})
	 * @IsGranted("IS_ANONYMOUS")
	 */
	public function emailExists(Request $request): Response {
		$email = $request->get('_email');
		if ($email === null) {
			return $this->json(['message' => 'Cannot read email data from client.', Response::HTTP_BAD_REQUEST]);
		}
		$user = $this->userRepository->findOneBy(['email' => $email]);
		if ($user !== null) {
			return $this->json(['message' => 'Email is already registered. Click below to reset your password.'], Response::HTTP_CONFLICT);
		}
		return $this->json(['message' => 'Email is available.'], Response::HTTP_OK);
	}
}