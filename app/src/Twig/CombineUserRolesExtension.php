<?php

declare(strict_types = 1);

namespace App\Twig;


use App\Entity\User;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CombineUserRolesExtension extends AbstractExtension {
	public function getFilters(): array {
		return [
			new TwigFilter('displayCombinedRoles', [$this, 'combineRoles']),
		];
	}
	
	public function combineRoles(mixed $user): string {
		if ($user === null || ! $user instanceof User) {
			return 'nobody';
		}
		if ($user->hasRole(User::ROLE_ADMIN)) {
			return 'Alpha & Omega';
		}
		if ($user->hasRole(User::ROLE_PROJECT_LEAD)) {
			return 'Glorious Leader of Projects';
		}
		return 'Worker Bee';
	}
}