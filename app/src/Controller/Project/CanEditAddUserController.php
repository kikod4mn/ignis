<?php

declare(strict_types = 1);

namespace App\Controller\Project;

use App\Entity\Project;
use App\Entity\User;
use App\Form\Project\ChooseUserAddType;
use App\Service\Contracts\Flashes;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function sprintf;

final class CanEditAddUserController extends AbstractController {
	public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger) { }
	
	/**
	 * @Route("/projects/{project_uuid}/can-edit/add/{user_uuid}", name="projects-edit-add", methods={"GET"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 * @ParamConverter("user", class="App\Entity\User", options={"mapping": {"user_uuid" = "uuid"}})
	 * @IsGranted("ROLE_EDIT_PROJECT", subject="project")
	 * @IsGranted("ROLE_PROJECT_LEAD")
	 */
	public function addEditor(Project $project, User $user): Response {
		$project->addCanEdit($user)->addCanView($user);
		$this->em->flush();
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Added user as a viewer and an editor to project.');
		/** @var User $lead */
		$lead = $this->getUser();
		$this->logger->info(sprintf('User %s has been added to project %s as an editor and viewer by %s', $user->getId(), $project->getId(), $lead->getId()));
		return $this->redirectToRoute('project-show', ['project_uuid' => $project->getUuid()]);
	}
	
	/**
	 * @Route("/projects/{project_uuid}/add-can-edit/choose", name="projects-edit-add-choose", methods={"GET", "POST"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 * @IsGranted("ROLE_EDIT_PROJECT", subject="project")
	 * @IsGranted("ROLE_PROJECT_LEAD")
	 */
	public function chooseEditor(Request $request, Project $project): Response {
		$chooseUserForm = $this->createForm(ChooseUserAddType::class, $project);
		$chooseUserForm->handleRequest($request);
		if ($chooseUserForm->isSubmitted() && $chooseUserForm->isValid()) {
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Added users as an editor to project.');
			/** @var User $lead */
			$lead = $this->getUser();
			foreach ($chooseUserForm->get('_canEdit')->getData() as $user) {
				$project->addCanEdit($user)->addCanView($user);
				$this->em->flush();
				$this->logger->info(sprintf('User %s has been added to project %s as an editor and viewer by %s', $user->getId(), $project->getId(), $lead->getId()));
			}
			return $this->redirectToRoute('project-show', ['project_uuid' => $project->getUuid()]);
		}
		return $this->render('projects/choose-editor.html.twig', ['project' => $project, 'chooseUserForm' => $chooseUserForm->createView()]);
	}
}