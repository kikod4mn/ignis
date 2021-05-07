<?php

declare(strict_types = 1);

namespace App\Controller\Project;

use App\Entity\Project;
use App\Entity\Role;
use App\Entity\User;
use App\Event\Creators\CreateEntityBackupEvent;
use App\Service\Contracts\Flashes;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use function sprintf;

class DeleteController extends AbstractController {
	public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger, private EventDispatcherInterface $ed) { }
	
	/**
	 * @Route("/projects/{project_uuid}/delete", name="project-delete", methods={"GET", "DELETE"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 */
	public function __invoke(Project $project): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseDelete($project);
		}
		if ($this->isGranted(Role::ROLE_DELETE_PROJECT, $project)) {
			return $this->delete($project);
		}
		if (! $this->isGranted(Role::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
	
	private function delete(Project $project): Response {
		$this->ed->dispatch(new CreateEntityBackupEvent($project));
		$this->em->remove($project);
		$this->em->flush();
		/** @var User $user */
		$user = $this->getUser();
		$this->logger->info(
			sprintf(
				'User "%s" with id "%d", removed project: "%s", with id: "%d".',
				$user->getName(), $user->getId(), $project->getName(), $project->getId()
			)
		);
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Deleted the project! It is now gone and forgotten!');
		// todo implement backup
		return $this->redirectToRoute('home');
	}
	
	private function showcaseDelete(Project $project): Response {
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Deleted the project! It is now gone and forgotten!');
		$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
		return $this->redirectToRoute('home');
	}
}