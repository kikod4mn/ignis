<?php

declare(strict_types = 1);

namespace App\Controller\User\Account;

use App\Repository\UserRepository;
use App\Service\Contracts\Flashes;
use App\Service\TimeCreator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class EmailConfirmController extends AbstractController {
	public function __construct(private UserRepository $userRepository, private EntityManagerInterface $em) { }
	
	/**
	 * @Route("/credentials/confirm/email/{token}", name="credentials-email-confirmation", methods={"GET"})
	 * @IsGranted("IS_ANONYMOUS")
	 */
	public function __invoke(string $token): Response {
		$user = $this->userRepository->findOneBy(['emailConfirmToken' => $token]);
		if ($user === null) {
			throw new CustomUserMessageAuthenticationException('User not found. Invalid token provided.');
		}
		$user
			->setEmailConfirmToken(null)
			->setEmailConfirmedAt(TimeCreator::now())
		;
		$this->em->flush();
		$this->addFlash(
			Flashes::WARNING_MESSAGE,
			sprintf(
				'Awesome %s, your email has been confirmed! After an admin activates your account, you will have full API access!',
				$user->getName()
			)
		);
		return $this->render('home/index.html.twig');
	}
}