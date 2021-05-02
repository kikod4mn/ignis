<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\Language;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class LanguageVoter extends Voter {
	/** @var array<int, string> */
	private array $attributes = [Role::ROLE_ADD_LANGUAGE, Role::ROLE_EDIT_LANGUAGE, Role::ROLE_DELETE_LANGUAGE];
	
	public function __construct(private Security $security) { }
	
	protected function supports($attribute, $subject): bool {
		return in_array($attribute, $this->attributes)
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
		if ($this->security->isGranted(Role::ROLE_ADMIN, $user)) {
			return true;
		}
		switch ($attribute) {
			case Role::ROLE_ADD_LANGUAGE:
				return $this->security->isGranted(Role::ROLE_ADD_LANGUAGE, $user);
			case Role::ROLE_EDIT_LANGUAGE:
				return $this->security->isGranted(Role::ROLE_EDIT_LANGUAGE, $user);
			case Role::ROLE_DELETE_LANGUAGE:
				return $this->security->isGranted(Role::ROLE_DELETE_LANGUAGE, $user);
		}
		
		return false;
	}
}
