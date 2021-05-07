<?php

declare(strict_types = 1);

namespace App\Entity\Concerns;

use App\Entity\Contracts\TimeStampableContract;
use App\Service\TimeCreator;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait TimestampsConcern {
	/**
	 * @ORM\Column(type="carbon_immutable", nullable=false)
	 */
	private ?DateTimeInterface $createdAt = null;
	
	/**
	 * @ORM\Column(type="carbon", nullable=true)
	 */
	private ?DateTimeInterface $updatedAt = null;
	
	public function getCreatedAt(): ?DateTimeInterface {
		return $this->createdAt;
	}
	
	/**
	 * @return TimeStampableContract|$this
	 */
	public function setCreatedAt(DateTimeInterface $createdAt): TimeStampableContract {
		$this->createdAt = $createdAt;
		return $this;
	}
	
	public function getUpdatedAt(): ?DateTimeInterface {
		return $this->updatedAt;
	}
	
	/**
	 * @return TimeStampableContract|$this
	 */
	public function setUpdatedAt(?DateTimeInterface $updatedAt): TimeStampableContract {
		$this->updatedAt = $updatedAt;
		return $this;
	}
	
	/**
	 * @return TimeStampableContract|$this
	 */
	public function setCreationTimestamps(): TimeStampableContract {
		$this->createdAt = TimeCreator::now();
		return $this;
	}
	
	/**
	 * @return TimeStampableContract|$this
	 */
	public function setUpdatedTimestamps(): TimeStampableContract {
		$this->updatedAt = TimeCreator::now();
		return $this;
	}
}