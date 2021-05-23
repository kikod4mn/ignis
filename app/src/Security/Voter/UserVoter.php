<?php

declare(strict_types = 1);

namespace App\Security\Voter;


use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter {
	/** @var array<int, string> */
	private array $attributes = [User::ROLE_EDIT_PROFILE, User::ROLE_EDIT_ACCOUNT, User::ROLE_DELETE_USER, User::ROLE_MODIFY_ROLES];
	
	protected function supports(string $attribute, mixed $subject): bool {
		return in_array($attribute, $this->attributes, true)
			   && $subject instanceof User;
	}
	
	/**
	 * @param   string           $attribute
	 * @param   User             $subject
	 * @param   TokenInterface   $token
	 * @return bool
	 */
	protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool {
		$user = $token->getUser();
		if (! $user instanceof User) {
			return false;
		}
		switch ($attribute) {
			case User::ROLE_EDIT_ACCOUNT:
			case User::ROLE_EDIT_PROFILE:
				return $subject->getId() === $user->getId() || $user->hasRole(User::ROLE_ADMIN);
			case User::ROLE_DELETE_USER:
			case User::ROLE_MODIFY_ROLES:
				return $user->hasRole(User::ROLE_ADMIN);
		}
		return false;
	}
}