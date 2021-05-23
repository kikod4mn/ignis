<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Doctrine;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;

class EnableFilterListener {
	public function __construct(private EntityManagerInterface $em, private Security $security) { }
	
	public function onKernelRequest(RequestEvent $request): void {
//		if (! $request->isMasterRequest()) {
//			return;
//		}
		// disable relevant filters for admins
//		if ($this->security->isGranted(User::ROLE_ADMIN)) {
//			return;
//		}
//		$filter = $this->em->getFilters()->enable('soft_deleted_filter');
//		$filter->setParameter('soft_deleted', 'false');
	}
}