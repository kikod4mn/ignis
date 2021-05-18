<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Feature;

/**
 * @return array<int, string>
 */

use App\Service\TimeCreator;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use function mt_rand;

class FeatureFixtures extends BaseFixture implements DependentFixtureInterface {
	public function loadData(): void {
		$this->createMany(
			Feature::class, 1211, function (Feature $feature): void {
			$isImplemented = (bool) mt_rand(0, 1);
			$feature->setImplemented($isImplemented);
			$feature->setImplementedAt($isImplemented ? TimeCreator::randomPast() : null);
			$feature->setTitle($this->getFaker()->sentence);
			$feature->setDescription($this->getFaker()->paragraph);
			$feature->setProject($this->getProject());
			$feature->setAuthor($this->getProjectLead());
			$feature->getProject()?->addCanView($feature->getAuthor());
			$feature->setCreationTimestamps();
			if (\mt_rand(0, 1) > 0) {
				$feature->setUpdatedTimestamps();
			}
			$feature->generateUuid();
		}
		);
		$this->createMany(
			Feature::class, 1211, function (Feature $feature): void {
			$isImplemented = (bool) mt_rand(0, 1);
			$feature->setImplemented($isImplemented);
			$feature->setImplementedAt($isImplemented ? TimeCreator::randomPast() : null);
			$feature->setTitle($this->getFaker()->sentence);
			$feature->setDescription($this->getFaker()->paragraph);
			$feature->setProject($this->getProject());
			$feature->setAuthor($this->getProjectLead());
			$feature->getProject()?->addCanView($feature->getAuthor());
			$feature->setCreationTimestamps();
			if (\mt_rand(0, 1) > 0) {
				$feature->setUpdatedTimestamps();
			}
			$feature->generateUuid();
			$feature->setSoftDeleted(true);
			$feature->setSoftDeletedAt(TimeCreator::now());
		}
		);
	}
	
	/**
	 * @return array<class-string<FixtureInterface>>
	 */
	public function getDependencies(): array {
		return [UserFixtures::class, ProjectFixtures::class, CategoryFixtures::class];
	}
}