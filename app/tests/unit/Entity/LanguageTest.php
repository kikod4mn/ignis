<?php

declare(strict_types = 1);

namespace App\Tests\unit\Entity;

use App\Entity\Language;
use App\Entity\Project;
use App\Tests\unit\Concerns\FakerConcern;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @covers \App\Entity\Language
 */
class LanguageTest extends TestCase {
	use FakerConcern;
	
	public function testNewLanguage(): void {
		$language = new Language();
		static::assertNull($language->getId());
		static::assertNull($language->getUuid());
		static::assertNull($language->getName());
		static::assertNull($language->getDescription());
		static::assertInstanceOf(ArrayCollection::class, $language->getProjects());
		static::assertCount(0, $language->getProjects());
	}
	
	public function testLanguageSetter(): void {
		$language = new Language();
		$language->generateUuid();
		static::assertInstanceOf(UuidInterface::class, $language->getUuid());
		$name = $this->getFaker()->unique()->word();
		$language->setName($name);
		static::assertEquals($name, $language->getName());
		/** @var string $description */
		$description = $this->getFaker()->unique()->words(75, true);
		$language->setDescription($description);
		static::assertEquals($description, $language->getDescription());
		$projectOne     = new Project();
		$projectOneName = $this->getFaker()->unique()->sentence();
		$projectOne->setName($projectOneName);
		$projectTwo     = new Project();
		$projectTwoName = $this->getFaker()->unique()->sentence();
		$projectTwo->setName($projectTwoName);
		$language->addProject($projectOne);
		$language->addProject($projectTwo);
		static::assertCount(2, $language->getProjects());
		static::assertInstanceOf(Project::class, $language->getProjects()[0]);
		static::assertInstanceOf(Project::class, $language->getProjects()[1]);
		static::assertEquals($projectOneName, $language->getProjects()[0]?->getName());
		static::assertEquals($projectTwoName, $language->getProjects()[1]?->getName());
	}
}