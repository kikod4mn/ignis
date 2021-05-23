<?php

declare(strict_types = 1);

namespace App\Controller\Category;

use App\Entity\Category;
use App\Entity\User;
use App\Event\Updators\DeleteEvent;
use App\Service\Contracts\Flashes;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeleteController extends AbstractController {
	public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger, private EventDispatcherInterface $ed) { }
	
	/**
	 * @Route("/categories/{category_uuid}/delete", name="category-delete", methods={"GET", "DELETE"})
	 * @ParamConverter("category", class="App\Entity\Category", options={"mapping": {"category_uuid": "uuid"}})
	 */
	public function __invoke(Category $category): Response {
		if ($this->isGranted(User::ROLE_TEST_USER)) {
			return $this->showcaseDelete();
		}
		if ($this->isGranted(User::ROLE_DELETE_CATEGORY, $category)) {
			return $this->delete($category);
		}
		if (! $this->isGranted(User::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
	
	private function delete(Category $category): Response {
		$this->ed->dispatch(new DeleteEvent($category));
		$this->em->flush();
		/** @var User $user */
		$user = $this->getUser();
		$this->logger->info(
			sprintf(
				'User "%s" with id "%d", deleted category: "%s", with id: "%d".',
				$user->getName(), $user->getId(), $category->getName(), $category->getId()
			)
		);
		if ($category->getHardDeleted()) {
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Deleted the category! It is now gone and forgotten!');
		} else {
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'The category is now soft deleted to trash! Only admins and category author can see it.');
		}
		return $this->redirectToRoute('categories-list');
	}
	
	private function showcaseDelete(): Response {
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Deleted the category! It is now gone and forgotten!');
		$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
		return $this->redirectToRoute('categories-list');
	}
}