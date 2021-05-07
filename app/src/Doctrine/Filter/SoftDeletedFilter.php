<?php

declare(strict_types = 1);

namespace App\Doctrine\Filter;

use App\Entity\Contracts\SoftDeleteContract;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class SoftDeletedFilter extends SQLFilter {
	public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string {
		if (! $targetEntity->reflClass->implementsInterface(SoftDeleteContract::class)) {
			return '';
		}
		return sprintf('%s.soft_deleted = %s', $targetTableAlias, $this->getParameter('soft_deleted'));
	}
}