<?php

declare(strict_types = 1);

namespace App\Controller\Home;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LearnMoreController extends AbstractController {
	/**
	 * @Route("/learn-more", name="learn-more", methods="GET")
	 * @Template("home/learn-more.html.twig")
	 */
	public function __invoke(): void { }
}