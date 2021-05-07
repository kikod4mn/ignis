<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Creators;

use App\Entity\Version;
use App\Entity\User;
use App\Event\Creators\VersionCreateEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use function get_class;

class VersionCreateSubscriber implements EventSubscriberInterface {
	public function __construct(private Security $security, private EntityManagerInterface $em) { }
	
	/**
	 * @return array<string, array<int, string>>
	 */
	public static function getSubscribedEvents(): array {
		return [VersionCreateEvent::class => ['created']];
	}
	
	public function created(VersionCreateEvent $event): void {
		$user = $this->security->getUser();
		if (! $user instanceof User) {
			return;
		}
		$entity  = $event->entity;
		$history = new Version();
		$history->generateUuid();
		$history->setCreationTimestamps();
		$history->setClassName(get_class($entity));
		$history->setEntityId($entity->getId());
		$history->setField($event->field);
		$history->setValue($event->value);
		$history->setModifiedBy($user);
		$this->em->persist($history);
		// assume this is done before an edited entity is persisted into the database
		// and thus we will not call flush here because it can fuck up the logic of the app
	}
}