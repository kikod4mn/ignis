<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Role;
use function count;

class RoleFixtures extends BaseFixture {
	public function loadData(): void {
		$count = count(Role::ROLES);
		$this->createMany(
			Role::class, $count, function (Role $role, int $i) {
			$role->generateUuid();
			$role->setName(Role::ROLES[$i]);
		}
		);
	}
}