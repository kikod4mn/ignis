<?php

declare(strict_types = 1);

namespace App\Controller\Feature;

use App\Entity\Project;

use App\Entity\User;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListController extends AbstractController {
	public function __construct(private PaginatorInterface $paginator) { }
	
	/**
	 * @Route("/projects/{project_uuid}/features/{page}", name="project-show-features", methods={"GET"}, defaults={"page" = 1}, requirements={"page"="\d+"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 */
	public function __invoke(Project $project, int $page): Response {
		if ($this->isGranted(User::ROLE_TEST_USER)) {
			return $this->showcaseList($project, $page);
		}
		//todo for now use the same method because no difference exists for feature listing for test user
		if ($this->isGranted(User::ROLE_VIEW_PROJECT, $project)) {
			return $this->showcaseList($project, $page);
		}
		if (! $this->isGranted(User::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
	
	private function showcaseList(Project $project, int $page): Response {
		return $this->render(
			'features/list.html.twig',
			[
				'project'    => $project,
				'pagination' => $this->paginator->paginate($project->getFeatures(), $page, 12),
			]
		);
	}
}