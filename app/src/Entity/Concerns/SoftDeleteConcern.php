<?php

declare(strict_types = 1);

namespace App\Entity\Concerns;

use App\Entity\Contracts\SoftDeleteContract;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait SoftDeleteConcern {
	/**
	 * @ORM\Column(type="boolean", nullable=false, options={"default":0})
	 */
	private bool $softDeleted = false;
	/**
	 * @ORM\Column(type="carbon_immutable", nullable=true)
	 */
	private ?DateTimeInterface $softDeletedAt = null;
	
	private string $deleteType = 'none';
	
	public function getSoftDeleted(): bool {
		return $this->softDeleted;
	}
	
	/**
	 * @return SoftDeleteContract|$this
	 */
	public function setSoftDeleted(bool $softDeleted): SoftDeleteContract {
		$this->softDeleted = $softDeleted;
		return $this;
	}
	
	public function getSoftDeletedAt(): ?DateTimeInterface {
		return $this->softDeletedAt;
	}
	
	/**
	 * @return SoftDeleteContract|$this
	 */
	public function setSoftDeletedAt(DateTimeInterface $softDeletedAt): SoftDeleteContract {
		$this->softDeletedAt = $softDeletedAt;
		return $this;
	}
	
	public function getHardDeleted(): bool {
		return $this->deleteType === SoftDeleteContract::HARD_DELETE;
	}
	
	/**
	 * @param   string   $deleteType   Must be a constant mapped on the SoftDeleteContract.
	 *                                 This functionality allows for removal from the database after soft delete is done.
	 * @return SoftDeleteContract|$this
	 */
	public function setHardDeleted(string $deleteType): SoftDeleteContract {
		$this->deleteType = $deleteType;
		return $this;
	}
}