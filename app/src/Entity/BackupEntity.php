<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Concerns\EntityIdConcern;
use App\Entity\Concerns\EntityTimestampsConcern;
use App\Entity\Contracts\EntityIdContract;
use App\Entity\Contracts\TimeStampableContract;
use App\Repository\BackupEntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BackupEntityRepository::class)
 */
class BackupEntity implements EntityIdContract, TimeStampableContract {
	use EntityIdConcern;
	use EntityTimestampsConcern;
	
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
	
	public function setObject(string $object): BackupEntity {
		$this->object = $object;
		return $this;
	}
	
	public function getDeletedBy(): ?User {
		return $this->deletedBy;
	}
	
	public function setDeletedBy(User $deletedBy): BackupEntity {
		$this->deletedBy = $deletedBy;
		return $this;
	}
}
