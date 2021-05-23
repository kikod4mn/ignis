<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\Category;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CategoryVoter extends Voter {
	/** @var array<int, string> */
	private array $attributes = [User::ROLE_ADD_CATEGORY, User::ROLE_EDIT_CATEGORY, User::ROLE_DELETE_CATEGORY];
	
	protected function supports($attribute, $subject): bool {
		return in_array($attribute, $this->attributes, true)
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
		switch ($attribute) {
			case User::ROLE_ADD_CATEGORY:
			case User::ROLE_EDIT_CATEGORY:
			case User::ROLE_DELETE_CATEGORY:
				return $user->hasRole(User::ROLE_ADMIN);
		}
		return false;
	}
}
