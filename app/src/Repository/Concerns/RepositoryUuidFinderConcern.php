<?php

declare(strict_types = 1);

namespace App\Repository\Concerns;

use App\Doctrine\UuidEncoder;

trait RepositoryUuidFinderConcern {
	protected UuidEncoder $uuidEncoder;
	
	public function findOneByEncodedUuid(string $encodedUuid): ?object {
		return $this->findOneBy(['uuid' => $this->uuidEncoder->decode($encodedUuid),]);
	}
}