<?php

declare(strict_types = 1);

namespace App\Entity\Contracts;

use Ramsey\Uuid\UuidInterface;

interface EntityIdContract {
	public function getId(): ?int;
	
	public function getUuid(): ?UuidInterface;
	
	public function generateUuid(): void;
}