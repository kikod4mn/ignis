<?php

declare(strict_types = 1);

namespace App\Controller\Home;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TermsAndConditionsController extends AbstractController {
	/**
	 * @Route("/terms-and-conditions", name="terms-and-conditions", methods="GET")
	 * @Template("home/terms-and-conditions.html.twig")
	 */
	public function __invoke(): void { }
}