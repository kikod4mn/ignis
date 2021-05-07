<?php

declare(strict_types = 1);

namespace App\Controller\Bug;

use App\Entity\Project;
use App\Entity\Role;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListController extends AbstractController {
	public function __construct(private PaginatorInterface $paginator) { }
	
	/**
	 * @Route("/projects/{project_uuid}/bugs/{page}", name="project-show-bugs", methods={"GET"}, defaults={"page" = 1}, requirements={"page"="\d+"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 */
	public function __invoke(Project $project, int $page): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseList($project, $page);
		}
		//todo for now use the same method because no difference exists for bug listing for test user
		if ($this->isGranted(Role::ROLE_VIEW_PROJECT, $project)) {
			return $this->showcaseList($project, $page);
		}
		if (! $this->isGranted(Role::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
	
	private function showcaseList(Project $project, int $page): Response {
		return $this->render(
			'bugs/list.html.twig',
			[
				'project'    => $project,
				'pagination' => $this->paginator->paginate($project->getBugs(), $page, 12),
			]
		);
	}
}