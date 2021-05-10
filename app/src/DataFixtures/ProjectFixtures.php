<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\Role;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use function mt_rand;

class ProjectFixtures extends BaseFixture implements DependentFixtureInterface {
	public function loadData(): void {
		$this->createMany(
			Project::class, 57, function (Project $project) {
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
			for ($i = 0; $i < mt_rand(5, 12); $i++) {
				$user = $this->getUser();
				if (! $user->hasRole(Role::ROLE_VIEW_PROJECT)) {
					$user->addRole($this->getRole(Role::ROLE_VIEW_PROJECT));
				}
				$project->addCanView($user);
			}
			for ($i = 0; $i < mt_rand(5, 12); $i++) {
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