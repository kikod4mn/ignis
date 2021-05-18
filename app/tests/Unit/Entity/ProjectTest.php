<?php

declare(strict_types = 1);

namespace App\Tests\unit\Entity;

use App\Entity\Bug;
use App\Entity\Category;
use App\Entity\Feature;
use App\Entity\Image;
use App\Entity\Language;
use App\Entity\Project;
use App\Entity\User;
use App\Service\TimeCreator;
use App\Tests\unit\Concerns\FakerConcern;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @covers \App\Entity\Project
 */
class ProjectTest extends TestCase {
	use FakerConcern;
	
	public function testNewProject(): void {
		$project = new Project();
		static::assertNull($project->getId());
		static::assertNull($project->getUuid());
		static::assertNull($project->getName());
		static::assertNull($project->getDescription());
		static::assertNull($project->getAuthor());
		static::assertNull($project->getCreatedAt());
		static::assertNull($project->getUpdatedAt());
		static::assertNull($project->getCoverImage());
		static::assertNull($project->getCategory());
		static::assertInstanceOf(ArrayCollection::class, $project->getBugs());
		static::assertInstanceOf(ArrayCollection::class, $project->getFeatures());
		static::assertInstanceOf(ArrayCollection::class, $project->getLanguages());
		static::assertInstanceOf(ArrayCollection::class, $project->getCanView());
		static::assertInstanceOf(ArrayCollection::class, $project->getCanEdit());
		static::assertCount(0, $project->getBugs());
		static::assertCount(0, $project->getFeatures());
		static::assertCount(0, $project->getLanguages());
		static::assertCount(0, $project->getCanView());
		static::assertCount(0, $project->getCanEdit());
	}
	
	public function testProjectSetters(): void {
		$project     = new Project();
		$name        = $this->getFaker()->unique()->sentence();
		$description = $this->getFaker()->unique()->paragraph();
		$author      = new User();
		$authorName  = $this->getFaker()->unique()->name();
		$author->setName($authorName);
		$project->generateUuid();
		static::assertInstanceOf(UuidInterface::class, $project->getUuid());
		$project->setName($name);
		static::assertEquals($name, $project->getName());
		$project->setDescription($description);
		static::assertEquals($description, $project->getDescription());
		$project->setAuthor($author);
		static::assertEquals($authorName, $project->getAuthor()?->getName());
		$imagePath = $this->getFaker()->unique()->filePath();
		$image     = new Image();
		$image->setPath($imagePath);
		$project->setCoverImage($image);
		static::assertEquals($imagePath, $project->getCoverImage()?->getPath());
		$created = TimeCreator::randomPast();
		$project->setCreatedAt($created);
		static::assertEquals($created->getTimestamp(), $project->getCreatedAt()?->getTimestamp());
		$updated = TimeCreator::randomFuture();
		$project->setUpdatedAt($updated);
		static::assertEquals($updated->getTimestamp(), $project->getUpdatedAt()?->getTimestamp());
		$categoryName = $this->getFaker()->unique()->word();
		$category     = new Category();
		$category->setName($categoryName);
		$project->setCategory($category);
		static::assertEquals($categoryName, $project->getCategory()?->getName());
	}
	
	public function testBugs(): void {
		$bugOne      = new Bug();
		$bugOneTitle = $this->getFaker()->unique()->sentence();
		$bugOne->setTitle($bugOneTitle);
		$bugTwo      = new Bug();
		$bugTwoTitle = $this->getFaker()->unique()->sentence();
		$bugTwo->setTitle($bugTwoTitle);
		$project = new Project();
		$project->addBug($bugOne);
		$project->addBug($bugTwo);
		static::assertCount(2, $project->getBugs());
		static::assertEquals($bugOneTitle, $project->getBugs()[0]?->getTitle());
		static::assertEquals($bugTwoTitle, $project->getBugs()[1]?->getTitle());
	}
	
	public function testFeatures(): void {
		$featureOne      = new Feature();
		$featureOneTitle = $this->getFaker()->unique()->sentence();
		$featureOne->setTitle($featureOneTitle);
		$featureTwo      = new Feature();
		$featureTwoTitle = $this->getFaker()->unique()->sentence();
		$featureTwo->setTitle($featureTwoTitle);
		$project = new Project();
		$project->addFeature($featureOne);
		$project->addFeature($featureTwo);
		static::assertCount(2, $project->getFeatures());
		static::assertEquals($featureOneTitle, $project->getFeatures()[0]?->getTitle());
		static::assertEquals($featureTwoTitle, $project->getFeatures()[1]?->getTitle());
	}
	
	public function testLanguages(): void {
		$languageOne     = new Language();
		$languageOneName = $this->getFaker()->unique()->sentence();
		$languageOne->setName($languageOneName);
		$languageTwo     = new Language();
		$languageTwoName = $this->getFaker()->unique()->sentence();
		$languageTwo->setName($languageTwoName);
		$project = new Project();
		$project->addLanguage($languageOne);
		$project->addLanguage($languageTwo);
		static::assertCount(2, $project->getLanguages());
		static::assertEquals($languageOneName, $project->getLanguages()[0]?->getName());
		static::assertEquals($languageTwoName, $project->getLanguages()[1]?->getName());
	}
	
	public function testCanEdit(): void {
		$userOne     = new User();
		$userOneName = $this->getFaker()->unique()->sentence();
		$userOne->setName($userOneName);
		$userTwo     = new User();
		$userTwoName = $this->getFaker()->unique()->sentence();
		$userTwo->setName($userTwoName);
		$project = new Project();
		$project->addCanEdit($userOne);
		$project->addCanEdit($userTwo);
		static::assertCount(2, $project->getCanEdit());
		static::assertEquals($userOneName, $project->getCanEdit()[0]?->getName());
		static::assertEquals($userTwoName, $project->getCanEdit()[1]?->getName());
	}
	
	public function testCanView(): void {
		$userOne     = new User();
		$userOneName = $this->getFaker()->unique()->sentence();
		$userOne->setName($userOneName);
		$userTwo     = new User();
		$userTwoName = $this->getFaker()->unique()->sentence();
		$userTwo->setName($userTwoName);
		$project = new Project();
		$project->addCanView($userOne);
		$project->addCanView($userTwo);
		static::assertCount(2, $project->getCanView());
		static::assertEquals($userOneName, $project->getCanView()[0]?->getName());
		static::assertEquals($userTwoName, $project->getCanView()[1]?->getName());
	}
}