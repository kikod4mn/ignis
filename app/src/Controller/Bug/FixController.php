<?php

declare(strict_types = 1);

namespace App\Controller\Bug;

use App\Entity\Bug;
use App\Entity\Project;
use App\Entity\Role;
use App\Entity\User;
use App\Service\Contracts\Flashes;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function dd;
use function sprintf;

class FixController extends AbstractController {
	public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger) { }
	
	/**
	 * @Route("/projects/{project_uuid}/bugs/{bug_uuid}/fix", name="bug-fix", methods={"GET"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 * @ParamConverter("bug", class="App\Entity\Bug", options={"mapping":{"bug_uuid" = "uuid"}})
	 */
	public function __invoke(Project $project, Bug $bug): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseFix($project);
		}
		if ($this->isGranted(Role::ROLE_PROJECT_LEAD)
			&& $this->isGranted(Role::ROLE_VIEW_PROJECT, $project)
			&& $this->isGranted(Role::ROLE_FIX_BUG, $bug)) {
			return $this->fix($bug, $project);
		}
		throw $this->createNotFoundException();
	}
	
	private function showcaseFix(Project $project): Response {
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Bug is fixed!');
		$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
		// todo implement backup
		return $this->redirectToRoute('project-show-bugs', ['project_uuid' => $project->getUuid()]);
	}
	
	private function fix(Bug $bug, Project $project): Response {
		$bug->setFixed(true);
		$this->em->flush();
		/** @var User $user */
		$user = $this->getUser();
		$this->logger->info(
			sprintf(
				'User "%s" with id "%d", marked as fixed bug: "%s", with id: "%d".',
				$user->getName(), $user->getId(), $bug->getTitle(), $bug->getId()
			)
		);
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Bug is fixed!');
		// todo implement backup
		return $this->redirectToRoute('project-show-bugs', ['project_uuid' => $project->getUuid()]);
	}
}