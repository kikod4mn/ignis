<?php

declare(strict_types = 1);

namespace App\Controller\User\Account;

use App\Controller\Concerns\FlashFormErrors;
use App\Entity\User;
use App\Event\User\Account\RegisterEvent;
use App\Form\User\Account\RegisterType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Security\EmailConfirmService;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

class RegisterController extends AbstractController {
	use FlashFormErrors;
	
	public function __construct(
		private TokenGenerator $tokenGenerator, private EntityManagerInterface $em, private RoleRepository $roleRepository,
		private LoggerInterface $logger, private UserRepository $userRepository, private EventDispatcherInterface $ed,
		private EmailConfirmService $emailConfirmService
	) {
	}
	
	/**
	 * @Route("/register", name="account-register", methods={"GET", "POST"})
	 */
	public function register(Request $request): Response {
		if (! $this->isGranted('IS_ANONYMOUS')) {
			throw $this->createNotFoundException();
		}
		$user = new User();
		$form = $this->createForm(RegisterType::class, $user);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$this->emailConfirmService->setTokenAndSendEmail($user);
			$this->ed->dispatch(new RegisterEvent($user));
			try {
				$this->em->persist($user);
				$this->em->flush();
			} catch (Throwable $e) {
				$this->logger->critical($e->getMessage());
				throw $this->createNotFoundException();
			}
			return $this->render('user/account/success.html.twig');
		}
		$this->flashFormErrors($form);
		return $this->render('user/security/register/register.html.twig', ['form' => $form]);
	}
	
	/**
	 * @Route("/register/check-email", name="account-register-check-email", methods={"POST"})
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