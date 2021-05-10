<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Doctrine;

use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;

class EnableFilterListener {
	public function __construct(private EntityManagerInterface $em, private Security $security) { }
	
	public function onKernelRequest(RequestEvent $request): void {
		// disable relevant filters for admins and project leads
		if ($this->security->isGranted(Role::ROLE_ADMIN) || $this->security->isGranted(Role::ROLE_PROJECT_LEAD)) {
			return;
		}
		$filter = $this->em->getFilters()->enable('soft_deleted_filter');
		$filter->setParameter('soft_deleted', 'false');
	}
}