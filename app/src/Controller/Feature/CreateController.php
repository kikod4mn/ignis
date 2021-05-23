<?php

declare(strict_types = 1);

namespace App\Controller\Feature;

use App\Controller\Concerns\FlashFormErrors;
use App\Entity\Feature;
use App\Entity\Project;
use App\Entity\User;
use App\Event\Creators\AuthorableCreateEvent;
use App\Event\Creators\IdCreateEvent;
use App\Event\Creators\TimeStampableCreatedEvent;
use App\Form\Feature\FeatureCreateType;
use App\Service\Contracts\Flashes;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class CreateController extends AbstractController {
	use FlashFormErrors;
	
	public function __construct(private EntityManagerInterface $em, private EventDispatcherInterface $ed, private LoggerInterface $logger) { }
	
	/**
	 * @Route("/projects/{project_uuid}/features/create", name="feature-create", methods={"GET", "POST"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 */
	public function __invoke(Request $request, Project $project): Response {
		if ($this->isGranted(User::ROLE_TEST_USER)) {
			return $this->showcaseCreate($request, $project);
		}
		if ($this->isGranted(User::ROLE_VIEW_PROJECT, $project)
			&& $this->isGranted(User::ROLE_ADD_FEATURE)
			&& $this->isGranted(User::ROLE_PROJECT_LEAD)
		) {
			return $this->create($request, $project);
		}
		if (! $this->isGranted(User::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
	
	private function create(Request $request, Project $project): Response {
		$feature = new Feature();
		$form    = $this->createForm(FeatureCreateType::class, $feature);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$feature->setProject($project);
			$this->ed->dispatch(new IdCreateEvent($feature));
			$this->ed->dispatch(new TimeStampableCreatedEvent($feature));
			$this->ed->dispatch(new AuthorableCreateEvent($feature));
			try {
				$this->em->persist($feature);
				$this->em->flush();
			} catch (Throwable $e) {
				$this->logger->critical($e->getMessage());
				throw $this->createNotFoundException();
			}
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Added a new feature for the project successfully.');
			return $this->redirectToRoute('project-show-features', ['project_uuid' => $project->getUuid()]);
		}
		$this->flashFormErrors($form);
		return $this->render('features/create.html.twig', ['featureCreateForm' => $form->createView(), 'project' => $project]);
	}
	
	private function showcaseCreate(Request $request, Project $project): Response {
		$feature = new Feature();
		$form    = $this->createForm(FeatureCreateType::class, $feature);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$feature->setProject($project);
			$this->ed->dispatch(new IdCreateEvent($feature));
			$this->ed->dispatch(new TimeStampableCreatedEvent($feature));
			$this->ed->dispatch(new AuthorableCreateEvent($feature));
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Added a new feature for the project successfully.');
			$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
			return $this->redirectToRoute('project-show-features', ['project_uuid' => $project->getUuid()]);
		}
		$this->flashFormErrors($form);
		return $this->render('features/create.html.twig', ['featureCreateForm' => $form->createView(), 'project' => $project]);
	}
}