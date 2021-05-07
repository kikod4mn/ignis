<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Concerns\IdConcern;
use App\Entity\Concerns\TimestampsConcern;
use App\Entity\Contracts\IdContract;
use App\Entity\Contracts\TimeStampableContract;
use App\Repository\VersionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VersionRepository::class)
 */
class Version implements IdContract, TimeStampableContract {
	use IdConcern;
	use TimestampsConcern;
	
	/**
	 * @ORM\Column(type="string", length=768, nullable=false)
	 */
	private ?string $className = null;
	
	/**
	 * @ORM\Column(type="bigint", nullable=false)
	 */
	private ?int $entityId = null;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	private ?string $field = null;
	
	/**
	 * @ORM\Column(type="text", length=20000, nullable=false)
	 */
	private ?string $value = null;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User")
	 */
	private ?User $modifiedBy = null;
	
	public function getClassName(): ?string {
		return $this->className;
	}
	
	public function setClassName(string $className): Version {
		$this->className = $className;
		return $this;
	}
	
	public function getEntityId(): ?int {
		return $this->entityId;
	}
	
	public function setEntityId(int $entityId): Version {
		$this->entityId = $entityId;
		return $this;
	}
	
	public function getField(): ?string {
		return $this->field;
	}
	
	public function setField(string $field): Version {
		$this->field = $field;
		return $this;
	}
	
	public function getValue(): ?string {
		return $this->value;
	}
	
	public function setValue(string $value): Version {
		$this->value = $value;
		return $this;
	}
	
	public function getModifiedBy(): ?User {
		return $this->modifiedBy;
	}
	
	public function setModifiedBy(User $modifiedBy): Version {
		$this->modifiedBy = $modifiedBy;
		return $this;
	}
	
}
