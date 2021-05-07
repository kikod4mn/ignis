<?php

declare(strict_types = 1);

namespace App\Controller\Bug;

use App\Entity\Bug;
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
	 * @Route("/projects/{project_uuid}/bugs/{bug_uuid}/delete", name="bug-delete", methods={"DELETE", "GET"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 * @ParamConverter("bug", class="App\Entity\Bug", options={"mapping":{"bug_uuid" = "uuid"}})
	 */
	public function __invoke(Project $project, Bug $bug): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseDelete($project);
		}
		if ($this->isGranted(Role::ROLE_VIEW_PROJECT, $project) && $this->isGranted(Role::ROLE_DELETE_BUG, $bug)) {
			return $this->delete($project, $bug);
		}
		if (! $this->isGranted(Role::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
	
	private function delete(Project $project, Bug $bug): Response {
		$this->ed->dispatch(new CreateEntityBackupEvent($bug));
		$this->em->remove($bug);
		$this->em->flush();
		/** @var User $user */
		$user = $this->getUser();
		$this->logger->info(
			sprintf(
				'User "%s" with id "%d", removed bug: "%s", with id: "%d".',
				$user->getName(), $user->getId(), $bug->getTitle(), $bug->getId()
			)
		);
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Deleted the bug! It is now gone and forgotten!');
		// todo implement backup
		return $this->redirectToRoute('project-show-bugs', ['project_uuid' => $project->getUuid()]);
	}
	
	private function showcaseDelete(Project $project): Response {
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Deleted the bug! It is now gone and forgotten!');
		$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
		return $this->redirectToRoute('project-show-bugs', ['project_uuid' => $project->getUuid()]);
	}
}