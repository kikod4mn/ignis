<?php

declare(strict_types = 1);

namespace App\Controller\Category;

use App\Controller\Concerns\FlashFormErrors;
use App\Entity\Category;
use App\Entity\Role;
use App\Event\Creators\IdCreateEvent;
use App\Form\Category\CategoryCreateType;
use App\Service\Contracts\Flashes;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

class CreateController extends AbstractController {
	use FlashFormErrors;
	public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger, private EventDispatcherInterface $ed) { }
	
	/**
	 * @Route("/categories/create", name="categories-create", methods={"GET", "POST"})
	 */
	public function __invoke(Request $request): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseCreate($request);
		}
		if ($this->isGranted(Role::ROLE_ADD_CATEGORY)) {
			return $this->create($request);
		}
		if (! $this->isGranted(Role::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
	
	private function create(Request $request): Response {
		$category = new Category();
		$form     = $this->createForm(CategoryCreateType::class, $category);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$this->ed->dispatch(new IdCreateEvent($category));
			try {
				$this->em->persist($category);
				$this->em->flush();
			} catch (Throwable $e) {
				$this->logger->critical($e->getMessage());
				throw $this->createNotFoundException();
			}
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Created a new category successfully.');
			return $this->redirectToRoute('categories-list');
		}
		$this->flashFormErrors($form);
		return $this->render('categories/create.html.twig', ['categoryCreateForm' => $form->createView()]);
	}
	
	private function showcaseCreate(Request $request): Response {
		$category = new Category();
		$form     = $this->createForm(CategoryCreateType::class, $category);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$this->ed->dispatch(new IdCreateEvent($category));
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Created a new category successfully.');
			$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
			return $this->redirectToRoute('categories-list');
		}
		$this->flashFormErrors($form);
		return $this->render('categories/create.html.twig', ['categoryCreateForm' => $form->createView()]);
	}
}