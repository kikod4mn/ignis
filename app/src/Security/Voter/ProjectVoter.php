<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\Project;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProjectVoter extends Voter {
	/**
	 * @var array<int, string>
	 */
	private array $attributes = [User::ROLE_PROJECT_LEAD, User::ROLE_VIEW_PROJECT, User::ROLE_ADD_PROJECT, User::ROLE_EDIT_PROJECT, User::ROLE_DELETE_PROJECT];
	
	protected function supports(string $attribute, mixed $subject): bool {
		return in_array($attribute, $this->attributes, true)
			   && $subject instanceof Project;
	}
	
	/**
	 * @param   string           $attribute
	 * @param   Project          $subject
	 * @param   TokenInterface   $token
	 * @return bool
	 */
	protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool {
		$user = $token->getUser();
		if (! $user instanceof User || $subject->getAuthor() === null) {
			return false;
		}
		if ($user->hasRole(User::ROLE_ADMIN) || $subject->getAuthor()->getId() === $user->getId()) {
			return true;
		}
		switch ($attribute) {
			case User::ROLE_ADD_PROJECT:
				return $user->hasRole(User::ROLE_PROJECT_LEAD);
			case User::ROLE_EDIT_PROJECT:
				return $subject->getCanEdit()->contains($user) && $subject->getCanView()->contains($user);
			case User::ROLE_VIEW_PROJECT:
				return $subject->getCanView()->contains($user) || $subject->getCanEdit()->contains($user);
			case User::ROLE_DELETE_PROJECT:
				return $subject->getAuthor()->getId() === $user->getId();
		}
		return false;
	}
}
