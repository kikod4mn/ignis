<?php

declare(strict_types = 1);

namespace App\Controller\Project;

use App\Entity\Project;
use App\Entity\Role;
use App\Event\Creators\EntityAuthorableCreatedEvent;
use App\Event\Creators\EntityIdCreatedEvent;
use App\Event\Creators\EntityTimeStampableCreatedEvent;
use App\Form\Project\ProjectCreateType;
use App\Service\Contracts\Flashes;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class CreateController extends AbstractController {
	public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger, private EventDispatcherInterface $ed) {
	}
	
	/**
	 * @Route("/projects/create", name="projects-create", methods={"GET", "POST"})
	 */
	public function __invoke(Request $request): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->createShowcase($request);
		}
		if ($this->isGranted(Role::ROLE_ADD_PROJECT)) {
			return $this->create($request);
		}
		throw $this->createNotFoundException();
	}
	
	private function create(Request $request): Response {
		$project = new Project();
		$form    = $this->createForm(ProjectCreateType::class, $project);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$this->ed->dispatch(new EntityIdCreatedEvent($project));
			$this->ed->dispatch(new EntityTimeStampableCreatedEvent($project));
			$this->ed->dispatch(new EntityAuthorableCreatedEvent($project));
			try {
				$this->em->persist($project);
				$this->em->flush();
			} catch (Throwable $e) {
				$this->logger->critical($e->getMessage());
				return throw $this->createNotFoundException();
			}
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Created a new project successfully.');
			return $this->redirectToRoute('projects-list');
		}
		return $this->render('projects/create.html.twig', ['projectCreateForm' => $form->createView()]);
	}
	
	private function createShowcase(Request $request): Response {
		$project = new Project();
		$form    = $this->createForm(ProjectCreateType::class, $project);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$this->ed->dispatch(new EntityIdCreatedEvent($project));
			$this->ed->dispatch(new EntityTimeStampableCreatedEvent($project));
			$this->ed->dispatch(new EntityAuthorableCreatedEvent($project));
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Created a new project successfully.');
			$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
			return $this->redirectToRoute('projects-list');
		}
		return $this->render('projects/create.html.twig', ['projectCreateForm' => $form->createView()]);
	}
}