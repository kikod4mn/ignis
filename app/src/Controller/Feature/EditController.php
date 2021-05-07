<?php

declare(strict_types = 1);

namespace App\Controller\Feature;

use App\Entity\Feature;
use App\Entity\Project;
use App\Entity\Role;
use App\Entity\User;
use App\Event\Creators\VersionCreateEvent;
use App\Event\Updators\TimeStampableUpdateEvent;
use App\Form\Feature\FeatureEditType;
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
use function sprintf;

class EditController extends AbstractController {
	public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger, private EventDispatcherInterface $ed) { }
	
	/**
	 * @Route("/projects/{project_uuid}/features/{feature_uuid}/edit", name="feature-edit", methods={"GET", "POST"})
	 * @ParamConverter("project", class="App\Entity\Project", options={"mapping": {"project_uuid" = "uuid"}})
	 * @ParamConverter("feature", class="App\Entity\Feature", options={"mapping": {"feature_uuid" = "uuid"}})
	 */
	public function __invoke(Request $request, Project $project, Feature $feature): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseEdit($request, $project, $feature);
		}
		if ($this->isGranted(Role::ROLE_VIEW_PROJECT, $project) && $this->isGranted(Role::ROLE_EDIT_FEATURE, $feature)) {
			return $this->edit($request, $project, $feature);
		}
		if (! $this->isGranted(Role::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
	
	public function edit(Request $request, Project $project, Feature $feature): Response {
		$oldTitle       = $feature->getTitle();
		$oldDescription = $feature->getDescription();
		$form           = $this->createForm(FeatureEditType::class, $feature);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$this->ed->dispatch(new TimeStampableUpdateEvent($feature));
			// todo temporary edit, fix to have a more automated workflow
			if ($feature->getTitle() !== $oldTitle) {
				$this->ed->dispatch(new VersionCreateEvent($feature, 'title', (string) $oldTitle));
			}
			if ($feature->getDescription() !== $oldDescription) {
				$this->ed->dispatch(new VersionCreateEvent($feature, 'description', (string) $oldDescription));
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
					'User "%s" with id "%d", edited feature: "%s", with id: "%d".',
					$user->getName(), $user->getId(), $feature->getTitle(), $feature->getId()
				)
			);
			// todo add backup
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Feature edited! Your changes were saved successfully.');
			return $this->redirectToRoute('project-show-features', ['project_uuid' => $project->getUuid()]);
		}
		return $this->render('features/edit.html.twig', ['feature' => $feature, 'project' => $project, 'featureEditForm' => $form->createView()]);
	}
	
	private function showcaseEdit(Request $request, Project $project, Feature $feature): Response {
		$featureClone = clone $feature;
		$form         = $this->createForm(FeatureEditType::class, $featureClone);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$this->ed->dispatch(new TimeStampableUpdateEvent($featureClone));
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Feature edited! Your changes were saved successfully.');
			$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
			return $this->render('features/test-show.html.twig', ['feature' => $feature]);
		}
		return $this->render('features/edit.html.twig', ['feature' => $featureClone, 'project' => $project, 'featureEditForm' => $form->createView()]);
	}
}