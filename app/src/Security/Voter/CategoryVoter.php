<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\Category;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class CategoryVoter extends Voter {
	/** @var array<int, string> */
	private array $attributes = [Role::ROLE_ADD_CATEGORY, Role::ROLE_EDIT_CATEGORY, Role::ROLE_DELETE_CATEGORY];
	// This also accounts for soft delete functionality where only admin and owner should see a trashed entity.
	public function __construct(private Security $security) { }
	
	protected function supports($attribute, $subject): bool {
		return in_array($attribute, $this->attributes)
			   && $subject instanceof Category;
	}
	
	/**
	 * @param   string           $attribute
	 * @param   Category         $subject
	 * @param   TokenInterface   $token
	 * @return bool
	 */
	protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool {
		$user = $token->getUser();
		if (! $user instanceof User) {
			return false;
		}
		// This also accounts for soft delete functionality where only admin and owner should see a trashed entity.
		if ($this->security->isGranted(Role::ROLE_ADMIN, $user)) {
			return true;
		}
		switch ($attribute) {
			case Role::ROLE_ADD_CATEGORY:
				return $this->security->isGranted(Role::ROLE_ADD_CATEGORY, $user);
			case Role::ROLE_EDIT_CATEGORY:
				return $this->security->isGranted(Role::ROLE_EDIT_CATEGORY, $user);
			case Role::ROLE_DELETE_CATEGORY:
				return $this->security->isGranted(Role::ROLE_DELETE_CATEGORY, $user);
		}
		
		return false;
	}
}
