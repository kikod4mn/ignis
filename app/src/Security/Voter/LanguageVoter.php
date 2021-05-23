<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\Language;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class LanguageVoter extends Voter {
	/** @var array<int, string> */
	private array $attributes = [User::ROLE_ADD_LANGUAGE, User::ROLE_EDIT_LANGUAGE, User::ROLE_DELETE_LANGUAGE];
	
	protected function supports($attribute, $subject): bool {
		return in_array($attribute, $this->attributes, true)
			   && $subject instanceof Language;
	}
	
	/**
	 * @param   string           $attribute
	 * @param   Language         $subject
	 * @param   TokenInterface   $token
	 * @return bool
	 */
	protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool {
		$user = $token->getUser();
		if (! $user instanceof User) {
			return false;
		}
		switch ($attribute) {
			case User::ROLE_ADD_LANGUAGE:
			case User::ROLE_EDIT_LANGUAGE:
			case User::ROLE_DELETE_LANGUAGE:
				return $user->hasRole(User::ROLE_ADMIN);
		}
		return false;
	}
}
