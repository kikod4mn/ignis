<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\Feature;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class FeatureVoter extends Voter {
	/** @var array<int, string> */
	private array $attributes = [Role::ROLE_ADD_FEATURE, Role::ROLE_EDIT_FEATURE, Role::ROLE_IMPLEMENT_FEATURE, Role::ROLE_DELETE_FEATURE];
	
	public function __construct(private Security $security) { }
	
	protected function supports($attribute, $subject): bool {
		return in_array($attribute, $this->attributes)
			   && $subject instanceof Feature;
	}
	
	/**
	 * @param   string           $attribute
	 * @param   Feature          $subject
	 * @param   TokenInterface   $token
	 * @return bool
	 */
	protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool {
		$user = $token->getUser();
		if (! $user instanceof User) {
			return false;
		}
		// This also accounts for soft delete functionality where only admin and owner should see a trashed entity.
		if ($this->security->isGranted(Role::ROLE_ADMIN, $user) || $subject->getAuthor()?->getId() === $user->getId()) {
			return true;
		}
		switch ($attribute) {
			case Role::ROLE_ADD_FEATURE:
				return $this->security->isGranted(Role::ROLE_ADD_FEATURE, $user);
			case Role::ROLE_EDIT_FEATURE:
				return $this->security->isGranted(Role::ROLE_EDIT_FEATURE, $user) && $subject->getAuthor()?->getId() === $user->getId();
			case Role::ROLE_IMPLEMENT_FEATURE:
				return $this->security->isGranted(Role::ROLE_IMPLEMENT_FEATURE, $user);
			case Role::ROLE_DELETE_FEATURE:
				return $this->security->isGranted(Role::ROLE_DELETE_FEATURE, $user) && $subject->getAuthor()?->getId() === $user->getId();
		}
		
		return false;
	}
}
