<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Bug;
use App\Service\TimeCreator;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;

class BugFixtures extends BaseFixture implements DependentFixtureInterface {
	public function loadData(): void {
		$this->createMany(
			Bug::class, 1121, function (Bug $bug): void {
			$isFixed = (bool) mt_rand(0, 1);
			$user    = $this->getUser();
			$bug->setAuthor($user);
			$bug->setFixed($isFixed);
			$bug->setFixedAt($isFixed ? TimeCreator::randomPast() : null);
			$bug->setTitle($this->getFaker()->sentence);
			$bug->setDescription($this->getFaker()->paragraph);
			$bug->setCreationTimestamps();
			if (mt_rand(0, 1) > 0) {
				$bug->setUpdatedTimestamps();
			}
			$bug->generateUuid();
			$project = $this->getProject();
			$project->addCanView($user);
			$bug->setProject($project);
		}
		);
		$this->createMany(
			Bug::class, 21, function (Bug $bug): void {
			$isFixed = (bool) mt_rand(0, 1);
			$user    = $this->getUser();
			$bug->setAuthor($user);
			$bug->setFixed($isFixed);
			$bug->setFixedAt($isFixed ? TimeCreator::randomPast() : null);
			$bug->setTitle($this->getFaker()->sentence);
			$bug->setDescription($this->getFaker()->paragraph);
			$bug->setCreationTimestamps();
			if (mt_rand(0, 1) > 0) {
				$bug->setUpdatedTimestamps();
			}
			$bug->setSoftDeleted(true);
			$bug->setSoftDeletedAt(TimeCreator::now());
			$bug->generateUuid();
			$project = $this->getProject();
			$project->addCanView($user);
			$bug->setProject($project);
		}
		);
	}
	
	/**
	 * @return array<class-string<FixtureInterface>>
	 */
	public function getDependencies(): array {
		return [UserFixtures::class, CategoryFixtures::class, ProjectFixtures::class];
	}
}