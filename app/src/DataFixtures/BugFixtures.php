<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Bug;
use App\Service\TimeCreator;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use function mt_rand;

class BugFixtures extends BaseFixture implements DependentFixtureInterface {
	public function loadData(): void {
		$this->createMany(
			Bug::class, 112, function (Bug $bug) {
			$isFixed = (bool) mt_rand(0, 1);
			$bug->setAuthor($this->getUser());
			$bug->setProject($this->getProject());
			$bug->setFixed($isFixed ? true : false);
			$bug->setFixedAt($isFixed ? TimeCreator::randomPast() : null);
			$bug->setTitle($this->getFaker()->sentence);
			$bug->setDescription($this->getFaker()->sentences(asText: true));
			$bug->setCreationTimestamps();
			if (\mt_rand(0, 1) > 0) {
				$bug->setUpdatedTimestamps();
			}
			$bug->generateUuid();
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