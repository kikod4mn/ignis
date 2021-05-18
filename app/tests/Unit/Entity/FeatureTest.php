<?php

declare(strict_types = 1);

namespace App\Tests\unit\Entity;

use App\Entity\Feature;
use App\Entity\Project;
use App\Entity\User;
use App\Service\TimeCreator;
use App\Tests\unit\Concerns\FakerConcern;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @covers \App\Entity\Feature
 */
class FeatureTest extends TestCase {
	use FakerConcern;
	
	public function testNewFeature(): void {
		$feature = new Feature();
		static::assertNull($feature->getId());
		static::assertNull($feature->getUuid());
		static::assertNull($feature->getTitle());
		static::assertNull($feature->getDescription());
		static::assertNull($feature->getProject());
		static::assertFalse($feature->isImplemented());
		static::assertNull($feature->getCreatedAt());
		static::assertNull($feature->getUpdatedAt());
		static::assertNull($feature->getImplementedAt());
		static::assertNull($feature->getAuthor());
	}
	
	public function testFeatureSetters(): void {
		$feature = new Feature();
		$feature->generateUuid();
		static::assertInstanceOf(UuidInterface::class, $feature->getUuid());
		$author     = new User();
		$authorName = 'author ser namus';
		$author->setName($authorName);
		$feature->setAuthor($author);
		static::assertEquals($authorName, $feature->getAuthor()?->getName());
		$title = $this->getFaker()->unique()->sentence();
		$feature->setTitle($title);
		static::assertEquals($title, $feature->getTitle());
		/** @var string $description */
		$description = $this->getFaker()->unique()->words(75, true);
		$feature->setDescription($description);
		static::assertEquals($description, $feature->getDescription());
		$project     = new Project();
		$projectName = 'name of project';
		$project->setName($projectName);
		$feature->setProject($project);
		static::assertEquals($projectName, $feature->getProject()?->getName());
		$feature->setImplemented(true);
		static::assertTrue($feature->isImplemented());
		$created = TimeCreator::randomPast();
		$feature->setCreatedAt($created);
		static::assertEquals($created->getTimestamp(), $feature->getCreatedAt()?->getTimestamp());
		$updated = TimeCreator::randomFuture();
		$feature->setUpdatedAt($updated);
		static::assertEquals($updated->getTimestamp(), $feature->getUpdatedAt()?->getTimestamp());
		$fixedAt = TimeCreator::randomPast();
		$feature->setImplementedAt($fixedAt);
		static::assertEquals($fixedAt->getTimestamp(), $feature->getImplementedAt()?->getTimestamp());
	}
}