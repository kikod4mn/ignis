<?php

declare(strict_types = 1);

namespace App\Controller\Language;

use App\Entity\Language;
use App\Entity\Role;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShowController extends AbstractController {
	/**
	 * @Route("/languages/{language_uuid}/show", name="language-show", methods="GET")
	 * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"language_uuid" = "uuid"}})
	 */
	public function __invoke(Language $language): Response {
		if ($this->isGranted(Role::ROLE_TEST_USER)) {
			return $this->showcaseShow($language);
		}
		if ($this->isGranted(Role::ROLE_PROJECT_LEAD)) {
			return $this->showcaseShow($language);
		}
		throw $this->createNotFoundException();
	}
	
	private function showcaseShow(Language $language): Response {
		return $this->render('languages/show.html.twig', ['language' => $language]);
	}
}