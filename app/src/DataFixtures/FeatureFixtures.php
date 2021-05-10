<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Feature;	/**
	 * @return array<int, string>
	 */
use App\Service\TimeCreator;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use function mt_rand;

class FeatureFixtures extends BaseFixture implements DependentFixtureInterface {
	public function loadData(): void {
		$this->createMany(
			Feature::class, 121, function (Feature $feature) {
			$isImplemented = (bool) mt_rand(0, 1);
			$feature->setImplemented($isImplemented ? true : false);
			$feature->setImplementedAt($isImplemented ? TimeCreator::randomPast() : null);
			$feature->setTitle($this->getFaker()->sentence);
			$feature->setDescription($this->getFaker()->sentences(asText: true));
			$feature->setProject($this->getProject());
			$feature->setAuthor($this->getProjectLead());
			$feature->setCreationTimestamps();
			if (\mt_rand(0, 1) > 0) {
				$feature->setUpdatedTimestamps();
			}
			$feature->generateUuid();
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