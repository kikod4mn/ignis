<?php

declare(strict_types = 1);

namespace App\Controller\Admin\User;


use App\Entity\User;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListController extends AbstractController {
	public function __construct(private UserRepository $userRepository, private PaginatorInterface $paginator) { }
	
	/**
	 * @Route("/admin/users/{page}", name="admin-users-list", methods={"GET"}, defaults={"page" = 1}, requirements={"page"="\d+"})
	 */
	public function __invoke(int $page): Response {
		if ($this->isGranted(User::ROLE_TEST_USER)) {
			return $this->showcaseList($page);
		}
		if ($this->isGranted(User::ROLE_ADMIN)) {
			return $this->showcaseList($page);
		}
		throw $this->createNotFoundException();
	}
	
	private function showcaseList(int $page): Response {
		$qb = $this->userRepository->createQueryBuilder('u')->select()->orderBy('u.id', 'DESC')->getQuery();
		return $this->render('admin/users/list.html.twig', ['pagination' => $this->paginator->paginate($qb, $page, 12)]);
	}
}