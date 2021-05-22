<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\Bug;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BugVoter extends Voter {
	/** @var array<int, string> */
	private array $attributes = [Role::ROLE_ADD_BUG, Role::ROLE_FIX_BUG, Role::ROLE_EDIT_BUG, Role::ROLE_DELETE_BUG];
	
	protected function supports(string $attribute, $subject): bool {
		return in_array($attribute, $this->attributes, true)
			   && $subject instanceof Bug;
	}
	
	/**
	 * @param   string           $attribute
	 * @param   Bug              $subject
	 * @param   TokenInterface   $token
	 * @return bool
	 */
	protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool {
		$user = $token->getUser();
		if (! $user instanceof User || $subject->getProject() === null || $subject->getAuthor() === null) {
			return false;
		}
		if ($user->hasRole(Role::ROLE_ADMIN) || $subject->getAuthor()->getId() === $user->getId()) {
			return true;
		}
		switch ($attribute) {
			case Role::ROLE_ADD_BUG:
				return $subject->getProject()->getCanView()->contains($user);
			case Role::ROLE_FIX_BUG:
			case Role::ROLE_EDIT_BUG:
			case Role::ROLE_DELETE_BUG:
				return $subject->getProject()->getCanEdit()->contains($user);
		}
		return false;
	}
}
