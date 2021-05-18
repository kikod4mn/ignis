<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Entity;

use App\Entity\Contracts\SoftDeleteContract;
use App\Event\Updators\DeleteEvent;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use function method_exists;
use function sprintf;

class SoftDeleteSubscriber implements EventSubscriber {
	public function __construct(private LoggerInterface $logger, private EventDispatcherInterface $ed) { }
	
	public function getSubscribedEvents(): array {
		return ['onFlush'];
	}
	
	/**
	 * This function enables soft delete to also work when accidentally not building for it in the controllers.
	 */
	public function onFlush(OnFlushEventArgs $args): void {
		$em  = $args->getEntityManager();
		$uow = $em->getUnitOfWork();
		foreach ($uow->getScheduledEntityDeletions() as $entity) {
			if (! $entity instanceof SoftDeleteContract) {
				return;
			}
			// Make sure entity is first soft deleted, if it isnt, soft delete it and schedule extra update
			if ((! $entity->getSoftDeleted() || $entity->getSoftDeletedAt() === null) && !$entity->getHardDeleted()) {
				$oldSoftDeleted   = $entity->getSoftDeleted();
				$oldSoftDeletedAt = $entity->getSoftDeletedAt();
				$this->ed->dispatch(new DeleteEvent($entity));
				$em->persist($entity);
				$uow->propertyChanged($entity, 'softDeleted', $oldSoftDeleted, $entity->getSoftDeleted());
				$uow->propertyChanged($entity, 'softDeletedAt', $oldSoftDeletedAt, $entity->getSoftDeletedAt());
				$uow->scheduleExtraUpdate($entity, ['softDeleted' => [$oldSoftDeleted, $entity->getSoftDeleted()]]);
				$uow->scheduleExtraUpdate($entity, ['softDeletedAt' => [$oldSoftDeletedAt, $entity->getSoftDeletedAt()]]);
				return;
			}
			$this->log($entity);
			// By now entity has soft delete fields, so just enable the final piece
			$entity->setHardDeleted(SoftDeleteContract::HARD_DELETE);
			// todo add backup functionality in here instead of in controllers if dealing with a backup entity
		}
	}
	
	// todo crappy demo func
	private function log(object $entity): void {
		if (method_exists($entity, '__toString')) {
			$this->logger->error(sprintf('Entity "%s" hard deleted.', $entity->__toString()));
		}
		if (method_exists($entity, 'getTitle')) {
			$this->logger->error(sprintf('Entity "%s" hard deleted.', $entity->getTitle()));
		}
		if (method_exists($entity, 'getName')) {
			$this->logger->error(sprintf('Entity "%s" hard deleted.', $entity->getName()));
		}
	}
}