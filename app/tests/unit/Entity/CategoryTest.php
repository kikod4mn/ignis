<?php

declare(strict_types = 1);

namespace App\Tests\unit\Entity;

use App\Entity\Category;
use App\Entity\Project;
use App\Tests\unit\Concerns\FakerConcern;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @covers \App\Entity\Category
 */
class CategoryTest extends TestCase {
	use FakerConcern;
	
	public function testNewCategory(): void {
		$category = new Category();
		static::assertNull($category->getId());
		static::assertNull($category->getUuid());
		static::assertNull($category->getName());
		static::assertTrue($category->getProjects() instanceof ArrayCollection);
		static::assertTrue($category->getProjects()->isEmpty());
	}
	
	public function testCategorySetters(): void {
		$category = new Category();
		$category->generateUuid();
		static::assertInstanceOf(UuidInterface::class, $category->getUuid());
		$name = $this->getFaker()->unique()->sentence();
		$category->setName($name);
		static::assertEquals($name, $category->getName());
		$projectOne     = new Project();
		$projectOneName = $this->getFaker()->unique()->sentence();
		$projectOne->setName($projectOneName);
		$projectTwo     = new Project();
		$projectTwoName = $this->getFaker()->unique()->sentence();
		$projectTwo->setName($projectTwoName);
		$category->addProject($projectOne);
		$category->addProject($projectTwo);
		static::assertCount(2, $category->getProjects());
		static::assertInstanceOf(Project::class, $category->getProjects()[0]);
		static::assertInstanceOf(Project::class, $category->getProjects()[1]);
		static::assertEquals($projectOneName, $category->getProjects()[0]?->getName());
		static::assertEquals($projectTwoName, $category->getProjects()[1]?->getName());
	}
}