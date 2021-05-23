<?php

declare(strict_types = 1);

namespace App\Controller\Category;

use App\Entity\Category;

use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShowController extends AbstractController {
	/**
	 * @Route("/categories/{category_uuid}/show", name="category-show", methods="GET")
	 * @ParamConverter("category", class="App\Entity\Category", options={"mapping": {"category_uuid" = "uuid"}})
	 */
	public function __invoke(Category $category): Response {
		if ($this->isGranted(User::ROLE_TEST_USER)) {
			return $this->showcaseShow($category);
		}
		//todo for now use the same method because no difference exists for category show for test user
		if ($this->isGranted(User::ROLE_PROJECT_LEAD)) {
			return $this->showcaseShow($category);
		}
		if (! $this->isGranted(User::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
	
	private function showcaseShow(Category $category): Response {
		return $this->render('categories/show.html.twig', ['category' => $category]);
	}
}