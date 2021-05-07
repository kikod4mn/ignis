<?php

declare(strict_types = 1);

namespace App\Controller\Feature;

use App\Entity\Feature;
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
	 * @Route("/projects/{project_uuid}/features/{feature_uuid}/delete", name="feature-delete", methods={"GET", "DELETE"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 * @ParamConverter("feature", class="App\Entity\Feature", options={"mapping": {"feature_uuid" = "uuid"}})
	 */
	public function __invoke(Project $project, Feature $feature): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseDelete($project);
		}
		if ($this->isGranted(Role::ROLE_VIEW_PROJECT, $project)
			&& $this->isGranted(Role::ROLE_DELETE_FEATURE, $feature)) {
			return $this->delete($feature, $project);
		}
		if (! $this->isGranted(Role::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
	
	private function delete(Feature $feature, Project $project): Response {
		$this->ed->dispatch(new CreateEntityBackupEvent($feature));
		$this->em->remove($feature);
		$this->em->flush();
		/** @var User $user */
		$user = $this->getUser();
		$this->logger->info(
			sprintf(
				'User "%s" with id "%d", deleted feature: "%s", with id: "%d".',
				$user->getName(), $user->getId(), $feature->getTitle(), $feature->getId()
			)
		);
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Deleted the feature! It is now gone and forgotten!');
		// todo implement backup
		return $this->redirectToRoute('project-show-features', ['project_uuid' => $project->getUuid()]);
	}
	
	private function showcaseDelete(Project $project): Response {
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Deleted the feature! It is now gone and forgotten!');
		$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
		return $this->redirectToRoute('project-show-features', ['project_uuid' => $project->getUuid()]);
	}
}