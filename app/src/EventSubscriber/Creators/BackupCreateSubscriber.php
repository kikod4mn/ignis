<?php

declare(strict_types = 1);

namespace App\EventSubscriber\Creators;

use App\Entity\Backup;
use App\Entity\User;
use App\Event\Creators\BackupCreateEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use function serialize;

class BackupCreateSubscriber implements EventSubscriberInterface {
	public function __construct(private Security $security, private EntityManagerInterface $em) { }
	
	/**
	 * @return array<string, array<int, string>>
	 */
	public static function getSubscribedEvents(): array {
		return [BackupCreateEvent::class => ['created']];
	}
	
	public function created(BackupCreateEvent $event): void {
		$user = $this->security->getUser();
		if (! $user instanceof User) {
			return;
		}
		$backup = new Backup();
		$backup->setCreationTimestamps();
		$backup->generateUuid();
		$backup->setDeletedBy($user);
		$backup->setObject(serialize($event->entity));
		$this->em->persist($backup);
		// assume this is done before an entity is removed from the database
		// and thus we will not call flush here because it can fuck up the logic of the app
	}
}