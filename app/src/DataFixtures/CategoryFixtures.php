<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Category;

class CategoryFixtures extends BaseFixture {
	public function loadData(): void {
		$this->createMany(
			Category::class, 15, function (Category $category) {
			$category
				->setName($this->getFaker()->unique()->word)
				->generateUuid()
			;
		}
		);
	}
}