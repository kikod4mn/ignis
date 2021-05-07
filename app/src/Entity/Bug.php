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
use App\Repository\BugRepository;
use App\Service\TimeCreator;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BugRepository::class)
 */
class Bug implements IdContract, TimeStampableContract, AuthorableContract, VersionableContract, SoftDeleteContract {
	use IdConcern;
	use TimestampsConcern;
	use AuthorConcern;
	use SoftDeleteConcern;
	
	/**
	 * @ORM\Column(type="string", length=140, nullable=false)
	 */
	private ?string $title = null;
	
	/**
	 * @ORM\Column(type="string", length=10000, nullable=false)
	 */
	private ?string $description = null;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="bugs")
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 */
	private ?User $author = null;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="bugs")
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 */
	private ?Project $project = null;
	
	/**
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	private bool $fixed = false;
	/**
	 * @ORM\Column(type="carbon", nullable=true)
	 */
	private ?DateTimeInterface $fixedAt = null;
	
	public function getTitle(): ?string {
		return $this->title;
	}
	
	public function setTitle(string $title): Bug {
		$this->title = $title;
		return $this;
	}
	
	public function getDescription(): ?string {
		return $this->description;
	}
	
	public function setDescription(string $description): Bug {
		$this->description = $description;
		return $this;
	}
	
	public function getProject(): ?Project {
		return $this->project;
	}
	
	public function setProject(?Project $project): Bug {
		$this->project = $project;
		return $this;
	}
	
	public function isFixed(): bool {
		return $this->fixed;
	}
	
	public function setFixed(bool $fixed): Bug {
		$this->fixed = $fixed;
		if ($fixed) {
			$this->setFixedAt(TimeCreator::now());
		} else {
			$this->setFixedAt(null);
		}
		return $this;
	}
	
	public function getFixedAt(): ?DateTimeInterface {
		return $this->fixedAt;
	}
	
	public function setFixedAt(?DateTimeInterface $fixedAt): Bug {
		$this->fixedAt = $fixedAt;
		return $this;
	}
}
