<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class UserVoter extends Voter {
	private array $attributes = [Role::ROLE_EDIT_PROFILE, Role::ROLE_EDIT_ACCOUNT, Role::ROLE_DELETE_USER, Role::ROLE_MODIFY_ROLES];
	
	public function __construct(private Security $security) { }
	
	public function supports(string $attribute, mixed $subject): bool {
		return in_array($attribute, $this->attributes)
			   && $subject instanceof User;
	}
	
	protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool {
		$user = $token->getUser();
		if (! $user instanceof User) {
			return false;
		}
		// This also accounts for soft delete functionality where only admin and owner should see a trashed entity.
		if ($this->security->isGranted(Role::ROLE_ADMIN, $user) || $subject->getId() === $user->getId()) {
			return true;
		}
		switch ($attribute) {
			case Role::ROLE_EDIT_ACCOUNT:
			case Role::ROLE_EDIT_PROFILE:
				return $subject->getId() === $user->getId();
			case Role::ROLE_DELETE_USER:
				return $this->security->isGranted(Role::ROLE_DELETE_USER, $user);
			case Role::ROLE_MODIFY_ROLES:
				return $this->security->isGranted(Role::ROLE_MODIFY_ROLES, $user);
		}
		
		return false;
	}
}