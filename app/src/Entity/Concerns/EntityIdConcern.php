<?php

declare(strict_types = 1);

namespace App\Entity\Concerns;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;

trait EntityIdConcern {
	/**
	 * @ORM\GeneratedValue(strategy="CUSTOM")
	 * @ORM\Column(type="uuid", unique=true)
	 * @ORM\CustomIdGenerator(class=UuidGenerator::class)
	 */
	private ?UuidInterface $uuid = null;
	
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 * @ORM\Column(type="bigint", options={"unsigned":true})
	 */
	private ?int $id = null;
	
	public function getId(): ?int {
		return $this->id;
	}
	
	public function getUuid(): ?UuidInterface {
		return $this->uuid;
	}
	
	public function generateUuid(): void {
		if ($this->getUuid() === null) {
			$this->uuid = Uuid::uuid4();
		}
	}
}