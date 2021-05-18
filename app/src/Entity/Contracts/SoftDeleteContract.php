<?php

declare(strict_types = 1);

namespace App\Entity\Contracts;

use DateTimeInterface;

interface SoftDeleteContract {
	public const HARD_DELETE = 'HARD_DELETE';
	
	public function getSoftDeleted(): bool;
	
	public function setSoftDeleted(bool $softDeleted): SoftDeleteContract;
	
	public function getSoftDeletedAt(): ?DateTimeInterface;
	
	public function setSoftDeletedAt(DateTimeInterface $softDeletedAt): SoftDeleteContract;
	
	public function getHardDeleted(): bool;
	
	public function setHardDeleted(string $deleteType): SoftDeleteContract;
}