<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Concerns\EntityAuthorConcern;
use App\Entity\Concerns\EntityIdConcern;
use App\Entity\Concerns\EntityTimestampsConcern;
use App\Entity\Contracts\EntityAuthorableContract;
use App\Entity\Contracts\EntityIdContract;
use App\Entity\Contracts\TimeStampableContract;
use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 */
class Image implements EntityIdContract, EntityAuthorableContract, TimeStampableContract {
	use EntityIdConcern;
	use EntityAuthorConcern;
	use EntityTimestampsConcern;
	
	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="images")
	 */
	private ?User $author = null;
	
	/**
	 * @ORM\Column(type="string", length=768)
	 */
	private ?string $path = null;
	
	/**
	 * @ORM\OneToOne(targetEntity="Project", mappedBy="coverImage")
	 */
	private ?Project $projectCover = null;
	
	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\User", mappedBy="avatar")
	 */
	private ?User $userAvatar = null;
	
	public function getPath(): ?string {
		return $this->path;
	}
	
	public function setPath(string $path): Image {
		$this->path = $path;
		return $this;
	}
	
	public function getProjectCover(): ?Project {
		return $this->projectCover;
	}
	
	public function setProjectCover(?Project $project): self {
		if ($project === null && $this->projectCover !== null) {
			$this->projectCover->setCoverImage(null);
		}
		if ($project !== null && $project->getCoverImage() !== $this) {
			$project->setCoverImage($this);
		}
		$this->projectCover = $project;
		return $this;
	}
	
	public function getUserAvatar(): ?User {
		return $this->userAvatar;
	}
	
	public function setUserAvatar(?User $user): Image {
		if ($user === null && $this->userAvatar !== null) {
			$this->userAvatar->setAvatar(null);
		}
		if ($user !== null && $user->getAvatar() !== $this) {
			$user->setAvatar($this);
		}
		$this->userAvatar = $user;
		return $this;
	}
}
