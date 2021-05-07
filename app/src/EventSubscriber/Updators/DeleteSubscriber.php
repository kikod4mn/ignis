<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Updators;

use App\Entity\Contracts\SoftDeleteContract;
use App\Event\Updators\DeleteEvent;
use App\Service\TimeCreator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeleteSubscriber implements EventSubscriberInterface {
	public function __construct(private EntityManagerInterface $em) { }
	
	public static function getSubscribedEvents(): array {
		return [DeleteEvent::class => ['delete', 9999]];
	}
	
	public function delete(DeleteEvent $event): void {
		$entity = $event->entity;
		if (! $entity instanceof SoftDeleteContract) {
			$this->em->remove($entity);
			return;
		}
		if (! $entity->getSoftDeleted() || $entity->getSoftDeletedAt() === null) {
			$entity->setSoftDeleted(true)->setSoftDeletedAt(TimeCreator::now());
			return;
		}
		// By this point, the entity should only be an entity already soft deleted, thus we set the hard delete and remove it finally.
		$entity->setHardDelete(SoftDeleteContract::HARD_DELETE);
		$this->em->remove($entity);
	}
}