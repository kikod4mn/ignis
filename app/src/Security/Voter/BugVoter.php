<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\Bug;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class BugVoter extends Voter {
	/** @var array<int, string> */
	private array $attributes = [Role::ROLE_ADD_BUG, Role::ROLE_FIX_BUG, Role::ROLE_EDIT_BUG, Role::ROLE_DELETE_BUG];
	
	public function __construct(private Security $security) { }
	
	protected function supports(string $attribute, $subject): bool {
		return in_array($attribute, $this->attributes)
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
		if (! $user instanceof User) {
			return false;
		}
		if ($this->security->isGranted(Role::ROLE_ADMIN, $user)) {
			return true;
		}
		switch ($attribute) {
			case Role::ROLE_ADD_BUG:
				return $this->security->isGranted(Role::ROLE_ADD_BUG, $user);
			case Role::ROLE_FIX_BUG:
				return $this->security->isGranted(Role::ROLE_FIX_BUG, $user)
					   && $this->security->isGranted(Role::ROLE_PROJECT_LEAD, $user);
			case Role::ROLE_EDIT_BUG:
				return $this->security->isGranted(Role::ROLE_EDIT_BUG, $user) && $subject->getAuthor()?->getId() === $user->getId();
			case Role::ROLE_DELETE_BUG:
				return $this->security->isGranted(Role::ROLE_DELETE_BUG, $user) && $subject->getAuthor()?->getId() === $user->getId();
		}
		
		return false;
	}
}
