<?php

declare(strict_types = 1);

namespace App\Controller\Project;

use App\Controller\Concerns\FlashFormErrors;
use App\Entity\Project;

use App\Entity\User;
use App\Form\Project\ChooseUserRemoveViewerType;
use App\Service\Contracts\Flashes;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RemoveViewerController extends AbstractController {
	use FlashFormErrors;
	
	public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger) { }
	
	/**
	 * @Route("/projects/{project_uuid}/remove-can-view/choose", name="projects-view-add-remove", methods={"GET", "POST"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 */
	public function chooseEditor(Request $request, Project $project): Response {
		if (! $this->isGranted(User::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		/** @var User $lead */
		$lead = $this->getUser();
		if ($this->isGranted(User::ROLE_PROJECT_LEAD) && $project->getAuthor()?->getId() === $lead->getId()) {
			$chooseUserForm = $this->createForm(ChooseUserRemoveViewerType::class, $project);
			$chooseUserForm->handleRequest($request);
			if ($chooseUserForm->isSubmitted() && $chooseUserForm->isValid()) {
				$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Removed users as a viewer to project.');
				foreach ($chooseUserForm->get('_canView')->getData() as $user) {
					$project->removeCanView($user);
					$this->logger->info(sprintf('User %s has been removed from project %s as a viewer by %s', $user->getId(), $project->getId(), $lead->getId()));
				}
				$this->em->flush();
				return $this->redirectToRoute('project-show', ['project_uuid' => $project->getUuid()]);
			}
			$this->flashFormErrors($chooseUserForm);
			return $this->render('projects/choose-viewer.html.twig', ['project' => $project, 'chooseUserForm' => $chooseUserForm->createView()]);
		}
		throw $this->createNotFoundException();
	}
}