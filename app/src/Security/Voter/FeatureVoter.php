<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\Feature;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class FeatureVoter extends Voter {
	/** @var array<int, string> */
	private array $attributes = [User::ROLE_ADD_FEATURE, User::ROLE_EDIT_FEATURE, User::ROLE_IMPLEMENT_FEATURE, User::ROLE_DELETE_FEATURE];
	
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
		if ($this->security->isGranted(User::ROLE_ADMIN, $user) || $subject->getAuthor()?->getId() === $user->getId()) {
			return true;
		}
		switch ($attribute) {
			case User::ROLE_ADD_FEATURE:
			case User::ROLE_EDIT_FEATURE:
			case User::ROLE_IMPLEMENT_FEATURE:
			case User::ROLE_DELETE_FEATURE:
				return $subject->getProject()->getCanEdit()->contains($user)
					   && $this->security->isGranted(User::ROLE_PROJECT_LEAD, $user);
			
		}
		return false;
	}
}
