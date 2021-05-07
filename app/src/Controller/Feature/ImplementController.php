<?php

declare(strict_types = 1);

namespace App\Controller\Feature;

use App\Entity\Feature;
use App\Entity\Project;
use App\Entity\Role;
use App\Entity\User;
use App\Service\Contracts\Flashes;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function dd;
use function sprintf;

class ImplementController extends AbstractController {
	public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger) { }
	
	/**
	 * @Route("/projects/{project_uuid}/features/{feature_uuid}/implement", name="feature-implement", methods={"GET"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 * @ParamConverter("feature", class="App\Entity\Feature", options={"mapping": {"feature_uuid" = "uuid"}})
	 */
	public function __invoke(Project $project, Feature $feature): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseImplement($project);
		}
		if ($this->isGranted(Role::ROLE_PROJECT_LEAD)
			&& $this->isGranted(Role::ROLE_VIEW_PROJECT, $project)
			&& $this->isGranted(Role::ROLE_IMPLEMENT_FEATURE, $feature)) {
			return $this->implement($feature, $project);
		}
		if (! $this->isGranted(Role::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
	
	private function implement(Feature $feature, Project $project): Response {
		$feature->setImplemented(true);
		$this->em->flush();
		/** @var User $user */
		$user = $this->getUser();
		$this->logger->info(
			sprintf(
				'User "%s" with id "%d", marked as implemented feature: "%s", with id: "%d".',
				$user->getName(), $user->getId(), $feature->getTitle(), $feature->getId()
			)
		);
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Feature is implemented.');
		// todo implement backup
		return $this->redirectToRoute('project-show-features', ['project_uuid' => $project->getUuid()]);
	}
	
	private function showcaseImplement(Project $project): Response {
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Feature is implemented.');
		$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
		return $this->redirectToRoute('project-show-features', ['project_uuid' => $project->getUuid()]);
	}
}