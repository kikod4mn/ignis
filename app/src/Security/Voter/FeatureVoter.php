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
		return in_array($attribute, $this->attributes, true)
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
		if (! $user instanceof User || $subject->getProject() === null) {
			return false;
		}
		if ($this->security->isGranted(Role::ROLE_ADMIN, $user) || $subject->getAuthor()?->getId() === $user->getId()) {
			return true;
		}
		switch ($attribute) {
			case Role::ROLE_ADD_FEATURE:
			case Role::ROLE_EDIT_FEATURE:
			case Role::ROLE_IMPLEMENT_FEATURE:
			case Role::ROLE_DELETE_FEATURE:
				return $subject->getProject()->getCanEdit()->contains($user)
					   && $this->security->isGranted(Role::ROLE_PROJECT_LEAD, $user);
			
		}
		return false;
	}
}
