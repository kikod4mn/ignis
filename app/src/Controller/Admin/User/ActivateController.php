<?php

declare(strict_types = 1);

namespace App\Controller\Admin\User;

use App\Entity\Role;
use App\Entity\User;
use App\Event\Security\ActivateEvent;
use App\Service\Contracts\Flashes;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use function sprintf;

class ActivateController extends AbstractController {
	public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger, private EventDispatcherInterface $ed) { }
	
	/**
	 * @Route("/admin/users/{user_uuid}/activate", name="admin-users-activate", methods={"GET"})
	 * @ParamConverter("user", class="App\Entity\User", options={"mapping": {"user_uuid" = "uuid"}})
	 */
	public function __invoke(User $user): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseActivate();
		}
		if ($this->isGranted(Role::ROLE_ADMIN)) {
			return $this->activate($user);
		}
		throw $this->createNotFoundException();
	}
	
	private function activate(User $user): Response {
		$user->setActive(true);
		// todo implement email on activation
		$this->ed->dispatch(new ActivateEvent($user));
		$this->em->flush();
		/** @var User $admin */
		$admin = $this->getUser();
		$this->logger->info(
			sprintf(
				'Administrator "%s", with id "%d" has activated user "%s", id "%d" account. This user can now view projects and add bugs.',
				$admin->getName(), $admin->getId(), $user->getName(), $user->getId()
			)
		);
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'User has been activated. This user can now login.');
		return $this->redirectToRoute('admin-users-list');
	}
	
	private function showcaseActivate(): Response {
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'User has been activated. This user can now login.');
		$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
		return $this->redirectToRoute('admin-users-list');
	}
}