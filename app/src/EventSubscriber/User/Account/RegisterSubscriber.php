<?php

declare(strict_types = 1);

namespace App\EventSubscriber\User\Account;

use App\Entity\Role;
use App\Event\Creators\IdCreateEvent;
use App\Event\Creators\TimeStampableCreatedEvent;
use App\Event\Security\PasswordHashEvent;
use App\Event\User\Account\RegisterEvent;
use App\Repository\RoleRepository;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RegisterSubscriber implements EventSubscriberInterface {
	public function __construct(private EventDispatcherInterface $ed, private RoleRepository $roleRepository) { }
	
	public static function getSubscribedEvents(): array {
		return [RegisterEvent::class => ['register', 9999], ['roles', 9998], ['notifyAdmin', 9997]];
	}
	
	public function register(RegisterEvent $event): void {
		$event->user->setAgreedToTermsAt(Carbon::now());
		$this->ed->dispatch(new IdCreateEvent($event->user));
		$this->ed->dispatch(new TimeStampableCreatedEvent($event->user));
		$this->ed->dispatch(new PasswordHashEvent($event->user));
	}
	
	public function roles(RegisterEvent $event): void {
		$roles = new ArrayCollection();
		$roles->add($this->roleRepository->findOneBy(['name' => Role::ROLE_USER]));
		$event->user->setRoles($roles);
	}
	
	public function notifyAdmin(RegisterEvent $event): void {
	
	}
}