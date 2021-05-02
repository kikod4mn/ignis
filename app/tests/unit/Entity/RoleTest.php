<?php

declare(strict_types = 1);

namespace App\Tests\unit\Entity;

use App\Entity\Role;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @covers \App\Entity\Role
 */
class RoleTest extends TestCase {
	public function testNewRole(): void {
		$role = new Role();
		static::assertNull($role->getId());
		static::assertNull($role->getUuid());
		static::assertNull($role->getUuid());
		static::assertNull($role->getName());
		static::assertCount(0, $role->getUsers());
	}
	
	public function testRoleSetters(): void {
		$role = new Role();
		$role->generateUuid();
		static::assertInstanceOf(UuidInterface::class, $role->getUuid());
		$roleName = 'ROLE_TEST';
		$role->setName($roleName);
		static::assertEquals($roleName, $role->getName());
	}
}