<?php

declare(strict_types = 1);

namespace App\Controller\Project;

use App\Entity\Project;
use App\Entity\Role;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShowController extends AbstractController {
	/**
	 * @Route("/projects/{project_uuid}/show", name="project-show", methods={"GET"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 */
	public function __invoke(Project $project): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseShow($project);
		}
		if ($this->isGranted(Role::ROLE_VIEW_PROJECT, $project)) {
			return $this->showcaseShow($project);
		}
		throw $this->createNotFoundException();
	}
	
	private function showcaseShow(Project $project): Response {
		return $this->render('projects/show.html.twig', ['project' => $project]);
	}
}