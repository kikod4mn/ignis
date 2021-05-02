<?php

declare(strict_types = 1);

namespace App\Controller\Feature;

use App\Entity\Feature;
use App\Entity\Project;
use App\Entity\Role;
use App\Event\Creators\EntityAuthorableCreatedEvent;
use App\Event\Creators\EntityIdCreatedEvent;
use App\Event\Creators\EntityTimeStampableCreatedEvent;
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
	public function __construct(private EntityManagerInterface $em, private EventDispatcherInterface $ed, private LoggerInterface $logger) { }
	
	/**
	 * @Route("/projects/{project_uuid}/features/create", name="feature-create", methods={"GET", "POST"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 */
	public function __invoke(Request $request, Project $project): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseCreate($request, $project);
		}
		if ($this->isGranted(Role::ROLE_VIEW_PROJECT, $project) && $this->isGranted(Role::ROLE_ADD_FEATURE)) {
			return $this->create($request, $project);
		}
		throw $this->createNotFoundException();
	}
	
	private function create(Request $request, Project $project): Response {
		$feature = new Feature();
		$form    = $this->createForm(FeatureCreateType::class, $feature);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$feature->setProject($project);
			$this->ed->dispatch(new EntityIdCreatedEvent($feature));
			$this->ed->dispatch(new EntityTimeStampableCreatedEvent($feature));
			$this->ed->dispatch(new EntityAuthorableCreatedEvent($feature));
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
		return $this->render('features/create.html.twig', ['featureCreateForm' => $form->createView(), 'project' => $project]);
	}
	
	private function showcaseCreate(Request $request, Project $project): Response {
		$feature = new Feature();
		$form    = $this->createForm(FeatureCreateType::class, $feature);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$feature->setProject($project);
			$this->ed->dispatch(new EntityIdCreatedEvent($feature));
			$this->ed->dispatch(new EntityTimeStampableCreatedEvent($feature));
			$this->ed->dispatch(new EntityAuthorableCreatedEvent($feature));
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Added a new feature for the project successfully.');
			$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
			return $this->redirectToRoute('project-show-features', ['project_uuid' => $project->getUuid()]);
		}
		return $this->render('features/create.html.twig', ['featureCreateForm' => $form->createView(), 'project' => $project]);
	}
}