<?php

declare(strict_types = 1);

namespace App\Controller\Home;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController {
	/**
	 * @Route("/", methods="GET", name="home")
	 * @Template("home/index.html.twig")
	 */
	public function __invoke(): void {
	}
}