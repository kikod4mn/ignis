<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Concerns\AuthorConcern;
use App\Entity\Concerns\IdConcern;
use App\Entity\Concerns\SoftDeleteConcern;
use App\Entity\Concerns\TimestampsConcern;
use App\Entity\Contracts\AuthorableContract;
use App\Entity\Contracts\SoftDeleteContract;
use App\Entity\Contracts\VersionableContract;
use App\Entity\Contracts\IdContract;
use App\Entity\Contracts\TimeStampableContract;
use App\Repository\FeatureRepository;
use App\Service\TimeCreator;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FeatureRepository::class)
 */
class Feature implements IdContract, TimeStampableContract, AuthorableContract, VersionableContract, SoftDeleteContract {
	use IdConcern;
	use TimestampsConcern;
	use AuthorConcern;
	use SoftDeleteConcern;
	
	/**
	 * @ORM\Column(type="string", length=140, nullable=false)
	 */
	private ?string $title = null;
	
	/**
	 * @ORM\Column(type="string", length=2024, nullable=false)
	 */
	private ?string $description = null;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="features")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private ?User $author = null;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="features")
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 */
	private ?Project $project = null;
	
	/**
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	private bool $implemented = false;
	
	/**
	 * @ORM\Column(type="carbon", nullable=true)
	 */
	private ?DateTimeInterface $implementedAt = null;
	
	public function getTitle(): ?string {
		return $this->title;
	}
	
	public function setTitle(string $title): Feature {
		$this->title = $title;
		return $this;
	}
	
	public function getDescription(): ?string {
		return $this->description;
	}
	
	public function setDescription(string $description): Feature {
		$this->description = $description;
		return $this;
	}
	
	public function getProject(): ?Project {
		return $this->project;
	}
	
	public function setProject(?Project $project): Feature {
		$this->project = $project;
		return $this;
	}
	
	public function isImplemented(): bool {
		return $this->implemented;
	}
	
	public function setImplemented(bool $implemented): Feature {
		$this->implemented = $implemented;
		if ($this->implemented) {
			$this->setImplementedAt(TimeCreator::now());
		} else {
			$this->setImplementedAt(null);
		}
		return $this;
	}
	
	public function getImplementedAt(): ?DateTimeInterface {
		return $this->implementedAt;
	}
	
	public function setImplementedAt(?DateTimeInterface $implementedAt): Feature {
		$this->implementedAt = $implementedAt;
		return $this;
	}
}
