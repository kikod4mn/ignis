<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Language;

class LanguageFixtures extends BaseFixture {
	public function loadData(): void {
		$this->createMany(
			Language::class, 21, function (Language $language) {
			$language->generateUuid();
			$language->setName($this->getFaker()->unique()->word);
			$language->setDescription($this->getFaker()->sentences(asText: true));
		}
		);
	}
}