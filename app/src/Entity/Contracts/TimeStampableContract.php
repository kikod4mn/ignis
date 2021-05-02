<?php

declare(strict_types = 1);

namespace App\Entity\Contracts;

use DateTimeInterface;

interface TimeStampableContract {
	public function getCreatedAt(): ?DateTimeInterface;
	
	public function setCreatedAt(DateTimeInterface $createdAt): TimeStampableContract;
	
	public function getUpdatedAt(): ?DateTimeInterface;
	
	public function setUpdatedAt(DateTimeInterface $updatedAt): TimeStampableContract;
	
	public function setCreationTimestamps(): TimeStampableContract;
	
	public function setUpdatedTimestamps(): TimeStampableContract;
}