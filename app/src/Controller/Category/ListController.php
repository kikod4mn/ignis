<?php

declare(strict_types = 1);

namespace App\Controller\Category;

use App\Entity\Role;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListController extends AbstractController {
	public function __construct(private CategoryRepository $categoryRepository, private PaginatorInterface $paginator) { }
	
	/**
	 * @Route("/categories/{page}", name="categories-list", methods={"GET"}, defaults={"page" = 1}, requirements={"page"="\d+"})
	 * @Template("categories/list.html.twig")
	 */
	public function __invoke(int $page): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseList($page);
		}
		//todo for now use the same method because no difference exists for category listing for test user
		if ($this->isGranted(Role::ROLE_PROJECT_LEAD)) {
			return $this->showcaseList($page);
		}
		if (! $this->isGranted(Role::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
	
	private function showcaseList(int $page): Response {
		$qb = $this->categoryRepository->createQueryBuilder('c')->select()->orderBy('c.id', 'ASC')->getQuery();
		return $this->render('categories/list.html.twig', ['pagination' => $this->paginator->paginate($qb, $page, 12)]);
	}
}