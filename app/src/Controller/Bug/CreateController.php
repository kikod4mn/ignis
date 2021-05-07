<?php

declare(strict_types = 1);

namespace App\Controller\Bug;

use App\Entity\Bug;
use App\Entity\Project;
use App\Entity\Role;
use App\Event\Creators\AuthorableCreateEvent;
use App\Event\Creators\IdCreateEvent;
use App\Event\Creators\TimeStampableCreatedEvent;
use App\Form\Bug\BugCreateType;
use App\Service\Contracts\Flashes;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

class CreateController extends AbstractController {
	public function __construct(private EntityManagerInterface $em, private EventDispatcherInterface $ed, private LoggerInterface $logger) { }
	
	/**
	 * @Route("/projects/{project_uuid}/bugs/create", name="bug-create", methods={"GET", "POST"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 */
	public function __invoke(Request $request, Project $project): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseCreate($request, $project);
		}
		if ($this->isGranted(Role::ROLE_VIEW_PROJECT, $project) && $this->isGranted(Role::ROLE_ADD_BUG)) {
			return $this->create($request, $project);
		}
		if (! $this->isGranted(Role::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
	
	private function create(Request $request, Project $project): Response {
		$bug  = new Bug();
		$form = $this->createForm(BugCreateType::class, $bug);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$bug->setProject($project);
			$this->ed->dispatch(new IdCreateEvent($bug));
			$this->ed->dispatch(new AuthorableCreateEvent($bug));
			$this->ed->dispatch(new TimeStampableCreatedEvent($bug));
			try {
				$this->em->persist($bug);
				$this->em->flush();
			} catch (Throwable $e) {
				$this->logger->critical($e->getMessage());
				throw $this->createNotFoundException();
			}
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Created a new bug report for this project! Thank you!');
			return $this->redirectToRoute('project-show-bugs', ['project_uuid' => $project->getUuid()]);
		}
		return $this->render('bugs/create.html.twig', ['bugCreateForm' => $form->createView(), 'project' => $project]);
	}
	
	private function showcaseCreate(Request $request, Project $project): Response {
		$bug  = new Bug();
		$form = $this->createForm(BugCreateType::class, $bug);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$bug->setProject($project);
			$this->ed->dispatch(new IdCreateEvent($bug));
			$this->ed->dispatch(new AuthorableCreateEvent($bug));
			$this->ed->dispatch(new TimeStampableCreatedEvent($bug));
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Created a new bug report for this project! Thank you!');
			$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
			return $this->render('bugs/test-show.html.twig', ['bug' => $bug]);
		}
		return $this->render('bugs/create.html.twig', ['bugCreateForm' => $form->createView(), 'project' => $project]);
	}
}