<?php

declare(strict_types = 1);

namespace App\Tests\unit\Entity;

use App\Entity\Version;
use PHPUnit\Framework\TestCase;

class HistoryEntityTest extends TestCase {
	public function testHistory():void {
		$entity = new Version();
		static::assertNull($entity->getId());
		static::assertNull($entity->getUuid());
		static::assertNull($entity->getCreatedAt());
		static::assertNull($entity->getUpdatedAt());
		static::assertNull($entity->getClassName());
		static::assertNull($entity->getEntityId());
		static::assertNull($entity->getField());
		static::assertNull($entity->getValue());
		static::assertNull($entity->getModifiedBy());
	}
}