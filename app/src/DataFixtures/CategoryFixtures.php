<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Category;
use App\Service\TimeCreator;

class CategoryFixtures extends BaseFixture {
	public function loadData(): void {
		$this->createMany(
			Category::class, 21, function (Category $category) {
			$category
				->setName($this->getFaker()->unique()->word)
				->generateUuid()
			;
		}
		);
		$this->createMany(
			Category::class, 9, function (Category $category) {
			$category
				->setSoftDeleted(true)
				->setSoftDeletedAt(TimeCreator::now())
				->setName($this->getFaker()->unique()->word)
				->generateUuid()
			;
		}
		);
	}
}