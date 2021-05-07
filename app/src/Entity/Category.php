<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Concerns\IdConcern;
use App\Entity\Concerns\SoftDeleteConcern;
use App\Entity\Contracts\SoftDeleteContract;
use App\Entity\Contracts\VersionableContract;
use App\Entity\Contracts\IdContract;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @UniqueEntity("name")
 */
class Category implements IdContract, VersionableContract, SoftDeleteContract {
	use IdConcern;
	use SoftDeleteConcern;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=false, unique=true)
	 */
	private ?string $name = null;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Project", mappedBy="category")
	 * @var Collection<int, Project>
	 */
	private Collection $projects;
	
	public function __construct() {
		$this->projects = new ArrayCollection();
	}
	
	public function getName(): ?string {
		return $this->name;
	}
	
	public function setName(?string $name): Category {
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return Collection<int, Project>
	 */
	public function getProjects(): Collection {
		return $this->projects;
	}
	
	/**
	 * @param   array<int, Project>   $projects
	 * @return $this
	 */
	public function setProjects(array $projects): self {
		$this->projects = new ArrayCollection($projects);
		return $this;
	}
	
	public function addProject(Project $project): self {
		if (! $this->projects->contains($project)) {
			$this->projects->add($project);
			$project->setCategory($this);
		}
		return $this;
	}
	
	public function removeProject(Project $project): self {
		if ($this->projects->removeElement($project)) {
			if ($project->getCategory() === $this) {
				$project->setCategory(null);
			}
		}
		return $this;
	}
}
