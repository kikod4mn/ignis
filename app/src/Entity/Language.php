<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Concerns\IdConcern;
use App\Entity\Concerns\SoftDeleteConcern;
use App\Entity\Contracts\SoftDeleteContract;
use App\Entity\Contracts\VersionableContract;
use App\Entity\Contracts\IdContract;
use App\Repository\LanguageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=LanguageRepository::class)
 * @UniqueEntity("name")
 */
class Language implements IdContract, VersionableContract, SoftDeleteContract {
	use IdConcern;
	use SoftDeleteConcern;
	
	/**
	 * @ORM\Column(type="string", length=50, nullable=false, unique=true)
	 */
	private ?string $name = null;
	
	/**
	 * @ORM\Column(type="string", length=450, nullable=true)
	 */
	private ?string $description = null;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Project", mappedBy="languages")
	 * @var Collection<int, Project>
	 */
	private Collection $projects;
	
	public function __construct() {
		$this->projects = new ArrayCollection();
	}
	
	public function getName(): ?string {
		return $this->name;
	}
	
	public function setName(?string $name): Language {
		$this->name = $name;
		return $this;
	}
	
	public function getDescription(): ?string {
		return $this->description;
	}
	
	public function setDescription(?string $description): Language {
		$this->description = $description;
		return $this;
	}
	
	/**
	 * @return Collection<int, Project>
	 */
	public function getProjects(): Collection {
		return $this->projects;
	}
	
	public function addProject(Project $project): self {
		if (! $this->projects->contains($project)) {
			$this->projects->add($project);
			$project->addLanguage($this);
		}
		return $this;
	}
	
	public function removeProject(Project $project): self {
		if ($this->projects->removeElement($project)) {
			$project->removeLanguage($this);
		}
		return $this;
	}
}
