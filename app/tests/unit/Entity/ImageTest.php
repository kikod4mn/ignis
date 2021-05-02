<?php

declare(strict_types = 1);

namespace App\Tests\unit\Entity;

use App\Entity\Image;
use App\Entity\Project;
use App\Entity\User;
use App\Service\TimeCreator;
use App\Tests\unit\Concerns\FakerConcern;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @covers  \App\Entity\Image
 */
class ImageTest extends TestCase {
	use FakerConcern;
	
	public function testNewImage(): void {
		$image = new Image();
		static::assertNull($image->getId());
		static::assertNull($image->getUuid());
		static::assertNull($image->getAuthor());
		static::assertNull($image->getPath());
		static::assertNull($image->getCreatedAt());
		static::assertNull($image->getUpdatedAt());
		static::assertNull($image->getProjectCover());
		static::assertNull($image->getUserAvatar());
	}
	
	public function testImageSetters(): void {
		$image = new Image();
		$image->generateUuid();
		static::assertInstanceOf(UuidInterface::class, $image->getUuid());
		$author     = new User();
		$authorName = $this->getFaker()->unique()->name();
		$author->setName($authorName);
		$image->setAuthor($author);
		static::assertEquals($authorName, $image->getAuthor()?->getName());
		$path = $this->getFaker()->unique()->filePath();
		$image->setPath($path);
		$created = TimeCreator::randomPast();
		$image->setCreatedAt($created);
		static::assertEquals($created->getTimestamp(), $image->getCreatedAt()?->getTimestamp());
		$updated = TimeCreator::randomFuture();
		$image->setUpdatedAt($updated);
		static::assertEquals($updated->getTimestamp(), $image->getUpdatedAt()?->getTimestamp());
		$project             = new Project();
		$coverForProjectName = $this->getFaker()->unique()->sentence();
		$project->setName($coverForProjectName);
		$image->setProjectCover($project);
		static::assertEquals($coverForProjectName, $image->getProjectCover()?->getName());
		$user     = new User();
		$userName = $this->getFaker()->unique()->name();
		$user->setName($userName);
		$image->setUserAvatar($user);
		static::assertEquals($userName, $image->getUserAvatar()?->getName());
	}
}