<?php

declare(strict_types = 1);

namespace App\Controller\Language;

use App\Entity\Role;
use App\Repository\LanguageRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListController extends AbstractController {
	public function __construct(private LanguageRepository $languageRepository, private PaginatorInterface $paginator) { }
	
	/**
	 * @Route("/languages/{page}", name="languages-list", methods={"GET"}, defaults={"page" = 1}, requirements={"page"="\d+"})
	 */
	public function __invoke(int $page): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseList($page);
		}
		if ($this->isGranted(Role::ROLE_PROJECT_LEAD)) {
			return $this->showcaseList($page);
		}
		throw $this->createNotFoundException();
	}
	
	private function showcaseList(int $page): Response {
		$qb = $this->languageRepository->createQueryBuilder('l')->select()->orderBy('l.id', 'ASC')->getQuery();
		return $this->render('languages/list.html.twig', ['pagination' => $this->paginator->paginate($qb, $page, 12)]);
	}
}