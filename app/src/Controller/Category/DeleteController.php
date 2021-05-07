<?php

declare(strict_types = 1);

namespace App\Controller\Category;

use App\Entity\Category;
use App\Entity\Role;
use App\Entity\User;
use App\Service\Contracts\Flashes;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;
use function sprintf;

class DeleteController extends AbstractController {
	public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger) { }
	
	/**
	 * @Route("/categories/{category_uuid}/delete", name="category-delete", methods={"GET", "DELETE"})
	 * @ParamConverter("category", class="App\Entity\Category", options={"mapping": {"category_uuid": "uuid"}})
	 */
	public function __invoke(Category $category): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseDelete();
		}
		if ($this->isGranted(Role::ROLE_PROJECT_LEAD) && $this->isGranted(Role::ROLE_DELETE_CATEGORY, $category)) {
			return $this->delete($category);
		}
		if (! $this->isGranted(Role::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
	
	private function delete(Category $category): Response {
		try {
			$this->em->remove($category);
			$this->em->flush();
		} catch (Throwable $e) {
			$this->logger->critical($e->getMessage());
			throw $this->createNotFoundException();
		}
		/** @var User $user */
		$user = $this->getUser();
		$this->logger->info(
			sprintf(
				'User "%s" with id "%d", deleted category: "%s", with id: "%d".',
				$user->getName(), $user->getId(), $category->getName(), $category->getId()
			)
		);
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Deleted the category! It is now gone and forgotten!');
		// todo implement backup
		return $this->redirectToRoute('categories-list');
	}
	
	private function showcaseDelete(): Response {
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Deleted the category! It is now gone and forgotten!');
		$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
		return $this->redirectToRoute('categories-list');
	}
}