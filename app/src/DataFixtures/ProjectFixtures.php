<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Project;
use App\Service\TimeCreator;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use function mt_rand;

class ProjectFixtures extends BaseFixture implements DependentFixtureInterface {
	public function loadData(): void {
		$this->createMany(
			Project::class, 257, function (Project $project) {
			$project->setAuthor($this->getProjectLead());
			$project->setCategory($this->getCategory());
			for ($i = 0; $i < mt_rand(4, 9); $i++) {
				$project->addLanguage($this->getLanguage());
			}
			$project->setName($this->getFaker()->words(3, true));
			$project->setDescription($this->getFaker()->sentences(12, true));
			$project->setCreationTimestamps();
			$project->setUpdatedTimestamps();
			$project->generateUuid();
			for ($i = 0; $i < mt_rand(3, 6); $i++) {
				$project->addCanView($this->getUser());
			}
			for ($i = 0; $i < mt_rand(3, 6); $i++) {
				$project->addCanEdit($this->getProjectLead());
			}
		}
		);
		$this->createMany(
			Project::class, 17, function (Project $project) {
			$project->setAuthor($this->getProjectLead());
			$project->setCategory($this->getCategory());
			for ($i = 0; $i < mt_rand(4, 9); $i++) {
				$project->addLanguage($this->getLanguage());
			}
			$project->setName($this->getFaker()->words(3, true));
			$project->setDescription($this->getFaker()->sentences(12, true));
			$project->setCreationTimestamps();
			$project->setUpdatedTimestamps();
			$project->generateUuid();
			$project->setSoftDeleted(true);
			$project->setSoftDeletedAt(TimeCreator::now());
			for ($i = 0; $i < mt_rand(3, 6); $i++) {
				$project->addCanView($this->getUser());
			}
			for ($i = 0; $i < mt_rand(3, 6); $i++) {
				$project->addCanEdit($this->getProjectLead());
			}
		}
		);
	}
	
	/**
	 * @return array<class-string<FixtureInterface>>
	 */
	public function getDependencies(): array {
		return [UserFixtures::class, CategoryFixtures::class, LanguageFixtures::class];
	}
}