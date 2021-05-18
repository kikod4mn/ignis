<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Language;
use App\Service\TimeCreator;

class LanguageFixtures extends BaseFixture {
	public function loadData(): void {
		$this->createMany(
			Language::class, 21, function (Language $language): void {
			$language->generateUuid();
			$language->setName($this->getFaker()->unique()->word);
			$language->setDescription($this->getFaker()->paragraph);
		}
		);
		$this->createMany(
			Language::class, 21, function (Language $language): void {
			$language->generateUuid();
			$language->setSoftDeleted(true);
			$language->setSoftDeletedAt(TimeCreator::now());
			$language->setName($this->getFaker()->unique()->word);
			$language->setDescription($this->getFaker()->paragraph);
		}
		);
	}
}