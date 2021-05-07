<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Concerns\IdConcern;
use App\Entity\Concerns\TimestampsConcern;
use App\Entity\Contracts\IdContract;
use App\Entity\Contracts\TimeStampableContract;
use App\Repository\BackupEntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BackupEntityRepository::class)
 */
class Backup implements IdContract, TimeStampableContract {
	use IdConcern;
	use TimestampsConcern;
	
	/**
	 * @ORM\Column(type="text", nullable=false)
	 */
	private ?string $object = null;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User")
	 */
	private ?User $deletedBy = null;
	
	public function getObject(): ?string {
		return $this->object;
	}
	
	public function setObject(string $object): Backup {
		$this->object = $object;
		return $this;
	}
	
	public function getDeletedBy(): ?User {
		return $this->deletedBy;
	}
	
	public function setDeletedBy(User $deletedBy): Backup {
		$this->deletedBy = $deletedBy;
		return $this;
	}
}
