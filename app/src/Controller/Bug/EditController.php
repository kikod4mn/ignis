<?php

declare(strict_types = 1);

namespace App\Controller\Bug;

use App\Controller\Concerns\FlashFormErrors;
use App\Entity\Bug;
use App\Entity\Project;

use App\Entity\User;
use App\Event\Creators\VersionCreateEvent;
use App\Event\Updators\TimeStampableUpdateEvent;
use App\Form\Bug\BugEditType;
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

class EditController extends AbstractController {
	use FlashFormErrors;
	
	public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger, private EventDispatcherInterface $ed) { }
	
	/**
	 * @Route("/projects/{project_uuid}/bugs/{bug_uuid}/edit", name="bug-edit", methods={"GET", "POST"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 * @ParamConverter("bug", class="App\Entity\Bug", options={"mapping":{"bug_uuid" = "uuid"}})
	 */
	public function __invoke(Request $request, Project $project, Bug $bug): Response {
		if ($this->isGranted(User::ROLE_TEST_USER)) {
			return $this->showcaseEdit($request, $project, $bug);
		}
		if ($this->isGranted(User::ROLE_VIEW_PROJECT, $project) && $this->isGranted(User::ROLE_EDIT_BUG, $bug)) {
			return $this->edit($request, $project, $bug);
		}
		if (! $this->isGranted(User::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
		
	}
	
	private function edit(Request $request, Project $project, Bug $bug): Response {
		$oldTitle       = $bug->getTitle();
		$oldDescription = $bug->getDescription();
		$form           = $this->createForm(BugEditType::class, $bug);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$this->ed->dispatch(new TimeStampableUpdateEvent($bug));
			// todo temporary edit, fix to have a more automated workflow
			if ($bug->getTitle() !== $oldTitle) {
				$this->ed->dispatch(new VersionCreateEvent($bug, 'title', (string) $oldTitle));
			}
			if ($bug->getDescription() !== $oldDescription) {
				$this->ed->dispatch(new VersionCreateEvent($bug, 'description', (string) $oldDescription));
			}
			try {
				$this->em->flush();
			} catch (Throwable $e) {
				$this->logger->critical($e->getMessage());
				throw $this->createNotFoundException();
			}
			/** @var User $user */
			$user = $this->getUser();
			$this->logger->info(
				sprintf(
					'User "%s" with id "%d", edited bug: "%s", with id: "%d".',
					$user->getName(), $user->getId(), $bug->getTitle(), $bug->getId()
				)
			);
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Bug edited! Your changes were saved successfully.');
			// todo implement backup
			return $this->redirectToRoute('project-show-bugs', ['project_uuid' => $project->getUuid()]);
		}
		$this->flashFormErrors($form);
		return $this->render('bugs/edit.html.twig', ['project' => $project, 'bug' => $bug, 'bugEditForm' => $form->createView()]);
	}
	
	private function showcaseEdit(Request $request, Project $project, Bug $bug): Response {
		$bugTemp = clone $bug;
		$form    = $this->createForm(BugEditType::class, $bugTemp);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Bug edited! Your changes were saved successfully.');
			$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
			return $this->render('bugs/test-show.html.twig', ['bug' => $bugTemp]);
		}
		$this->flashFormErrors($form);
		return $this->render('bugs/edit.html.twig', ['project' => $project, 'bug' => $bugTemp, 'bugEditForm' => $form->createView()]);
	}
}