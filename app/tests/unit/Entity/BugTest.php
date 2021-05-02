<?php

declare(strict_types = 1);

namespace App\Tests\unit\Entity;

use App\Entity\Bug;
use App\Entity\Project;
use App\Entity\User;
use App\Service\TimeCreator;
use App\Tests\unit\Concerns\FakerConcern;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @covers \App\Entity\Bug
 */
class BugTest extends TestCase {
	use FakerConcern;
	
	public function testNewBug(): void {
		$bug = new Bug();
		static::assertNull($bug->getId());
		static::assertNull($bug->getUuid());
		static::assertNull($bug->getAuthor());
		static::assertNull($bug->getTitle());
		static::assertNull($bug->getDescription());
		static::assertFalse($bug->isFixed());
		static::assertNull($bug->getProject());
		static::assertNull($bug->getCreatedAt());
		static::assertNull($bug->getUpdatedAt());
		static::assertNull($bug->getFixedAt());
	}
	
	public function testBugSetters(): void {
		$bug        = new Bug();
		$bug->generateUuid();
		static::assertInstanceOf(UuidInterface::class, $bug->getUuid());
		$author     = new User();
		$authorName = 'author ser namus';
		$author->setName($authorName);
		$bug->setAuthor($author);
		static::assertEquals($authorName, $bug->getAuthor()?->getName());
		$title = $this->getFaker()->unique()->sentence();
		$bug->setTitle($title);
		static::assertEquals($title, $bug->getTitle());
		/** @var string $description */
		$description = $this->getFaker()->unique()->words(75, true);
		$bug->setDescription($description);
		static::assertEquals($description, $bug->getDescription());
		$project     = new Project();
		$projectName = 'name of project';
		$project->setName($projectName);
		$bug->setProject($project);
		static::assertEquals($projectName, $bug->getProject()?->getName());
		$created = TimeCreator::randomPast();
		$bug->setCreatedAt($created);
		static::assertEquals($created->getTimestamp(), $bug->getCreatedAt()?->getTimestamp());
		$updated = TimeCreator::randomFuture();
		$bug->setUpdatedAt($updated);
		static::assertEquals($updated->getTimestamp(), $bug->getUpdatedAt()?->getTimestamp());
		$bug->setFixed(true);
		static::assertTrue($bug->isFixed());
		$fixedAt = TimeCreator::randomPast();
		$bug->setFixedAt($fixedAt);
		static::assertEquals($fixedAt->getTimestamp(), $bug->getFixedAt()?->getTimestamp());
	}
}