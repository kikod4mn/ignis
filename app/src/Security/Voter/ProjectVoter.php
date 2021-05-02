<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\Project;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class ProjectVoter extends Voter {
	/**
	 * @var array<int, string>
	 *                        [ ROLE_USER, ROLE_FIX_BUG, ROLE_CREATE_PROJECTS, ROLE_ADD_FEATURE,
	 * ROLE_EDIT_FEATURE, ROLE_CREATE_CATEGORY, ROLE_EDIT_CATEGORY, ROLE_ADD_LANGUAGE, ROLE_EDIT_LANGUAGE ]
	 */
	private array $attributes = [Role::ROLE_VIEW_PROJECT, Role::ROLE_ADD_PROJECT, Role::ROLE_EDIT_PROJECT, Role::ROLE_DELETE_PROJECT];
	
	public function __construct(private RoleRepository $roleRepository, private Security $security) { }
	
	public function supports(string $attribute, mixed $subject): bool {
		return in_array($attribute, $this->attributes)
			   && $subject instanceof Project;
	}
	
	/**
	 * @param   string           $attribute
	 * @param   Project          $subject
	 * @param   TokenInterface   $token
	 * @return bool
	 */
	public function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool {
		$user = $token->getUser();
		if (! $user instanceof User) {
			return false;
		}
		if ($this->security->isGranted(Role::ROLE_ADMIN, $user) || $subject->getAuthor()?->getId() === $user->getId()) {
			return true;
		}
		switch ($attribute) {
			case Role::ROLE_ADD_PROJECT:
				return $this->security->isGranted(Role::ROLE_ADD_PROJECT, $user);
			case Role::ROLE_EDIT_PROJECT:
				return $this->security->isGranted(Role::ROLE_EDIT_PROJECT, $user)
					   && $subject->getCanEdit()->contains($user);
			case Role::ROLE_VIEW_PROJECT:
				return $this->security->isGranted(Role::ROLE_VIEW_PROJECT, $user)
					   && $subject->getCanView()->contains($user) || $subject->getCanEdit()->contains($user);
			case Role::ROLE_DELETE_PROJECT:
				return $this->security->isGranted(Role::ROLE_DELETE_PROJECT, $user)
					   && $subject->getAuthor()?->getId() === $user->getId();
		}
		
		return false;
	}
}
