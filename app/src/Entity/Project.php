<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Concerns\EntityAuthorConcern;
use App\Entity\Concerns\EntityIdConcern;
use App\Entity\Concerns\EntityTimestampsConcern;
use App\Entity\Contracts\EntityAuthorableContract;
use App\Entity\Contracts\EntityBackupContract;
use App\Entity\Contracts\EntityHistoryContract;
use App\Entity\Contracts\EntityIdContract;
use App\Entity\Contracts\TimeStampableContract;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 */
class Project implements EntityIdContract, TimeStampableContract, EntityAuthorableContract, EntityHistoryContract, EntityBackupContract {
	use EntityTimestampsConcern;
	use EntityAuthorConcern;
	use EntityIdConcern;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	private ?string $name = null;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="projects")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	private ?Category $category = null;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Language", inversedBy="projects")
	 * @ORM\JoinTable(
	 *     name="project_languages",
	 *     joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="language_id", referencedColumnName="id")}
	 *     )
	 * @var Collection<int, Language>
	 */
	private Collection $languages;
	
	/**
	 * @ORM\Column(type="string", length=1026, nullable=false)
	 */
	private ?string $description = null;
	
	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\Image", inversedBy="projectCover")
	 */
	private ?Image $coverImage = null;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="projects")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private ?User $author = null;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Bug", mappedBy="project")
	 * @var Collection<int, Bug>
	 */
	private Collection $bugs;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Feature", mappedBy="project")
	 * @var Collection<int, Feature>
	 */
	private Collection $features;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="viewableProjects")
	 * @ORM\JoinTable(
	 *     name="project_user_view",
	 *     joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
	 * )
	 * @var Collection<int, User>
	 */
	private Collection $canView;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="editableProjects")
	 * @ORM\JoinTable(
	 *     name="project_user_edit",
	 *     joinColumns={ @ORM\JoinColumn(name="project_id", referencedColumnName="id")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
	 * )
	 * @var Collection<int, User>
	 */
	private Collection $canEdit;
	
	public function __construct() {
		$this->languages = new ArrayCollection();
		$this->bugs      = new ArrayCollection();
		$this->features  = new ArrayCollection();
		$this->canView   = new ArrayCollection();
		$this->canEdit   = new ArrayCollection();
	}
	
	public function getName(): ?string {
		return $this->name;
	}
	
	public function setName(string $name): Project {
		$this->name = $name;
		return $this;
	}
	
	public function getCategory(): ?Category {
		return $this->category;
	}
	
	public function setCategory(?Category $category): Project {
		$this->category = $category;
		return $this;
	}
	
	/**
	 * @return Collection<int, Language>
	 */
	public function getLanguages(): Collection {
		return $this->languages;
	}
	
	/**
	 * @param   array<int, Language>   $langs
	 * @return $this
	 */
	public function setLanguages(array $langs): self {
		$this->languages = new ArrayCollection($langs);
		return $this;
	}
	
	public function addLanguage(Language $language): Project {
		if (! $this->languages->contains($language)) {
			$this->languages->add($language);
		}
		return $this;
	}
	
	public function removeLanguage(Language $language): Project {
		if ($this->languages->contains($language)) {
			$this->languages->removeElement($language);
		}
		return $this;
	}
	
	public function getDescription(): ?string {
		return $this->description;
	}
	
	public function setDescription(string $description): Project {
		$this->description = $description;
		return $this;
	}
	
	public function displayCoverImage(): string {
		if (! $this->coverImage) {
			return 'build/images/_defaults/_default-cover-image.png';
		}
		return (string) $this->coverImage->getPath();
	}
	
	public function getCoverImage(): ?Image {
		return $this->coverImage;
	}
	
	public function setCoverImage(?Image $coverImage): self {
		$this->coverImage = $coverImage;
		return $this;
	}
	
	/**
	 * @return Collection<int, Bug>
	 */
	public function getBugs(): Collection {
		return $this->bugs;
	}
	
	/**
	 * @param   array<int, Bug>   $bugs
	 * @return $this
	 */
	public function setBugs(array $bugs): self {
		$this->bugs = new ArrayCollection($bugs);
		return $this;
	}
	
	public function addBug(Bug $bug): self {
		if (! $this->bugs->contains($bug)) {
			$this->bugs->add($bug);
			$bug->setProject($this);
		}
		return $this;
	}
	
	public function removeBug(Bug $bug): self {
		if ($this->bugs->removeElement($bug)) {
			if ($bug->getProject() === $this) {
				$bug->setProject(null);
			}
		}
		return $this;
	}
	
	/**
	 * @return Collection<int, Feature>
	 */
	public function getFeatures(): Collection {
		return $this->features;
	}
	
	/**
	 * @param   array<int, Feature>   $features
	 * @return $this
	 */
	public function setFeatures(array $features): self {
		$this->features = new ArrayCollection($features);
		return $this;
	}
	
	public function addFeature(Feature $feature): self {
		if (! $this->features->contains($feature)) {
			$this->features->add($feature);
			$feature->setProject($this);
		}
		return $this;
	}
	
	public function removeFeature(Feature $feature): self {
		if ($this->features->removeElement($feature)) {
			if ($feature->getProject() === $this) {
				$feature->setProject(null);
			}
		}
		return $this;
	}
	
	/**
	 * @return Collection<int, User>
	 */
	public function getCanView(): Collection {
		return $this->canView;
	}
	
	public function addCanView(User $user): self {
		if (! $this->canView->contains($user)) {
			$this->canView->add($user);
			if (! $user->getViewableProjects()->contains($this)) {
				$user->addViewableProject($this);
			}
		}
		return $this;
	}
	
	public function removeCanView(User $user): self {
		if ($this->canView->contains($user)) {
			$this->canView->removeElement($user);
			if ($user->getViewableProjects()->contains($this)) {
				$user->removeViewableProject($this);
			}
		}
		return $this;
	}
	
	/**
	 * @return Collection<int, User>
	 */
	public function getCanEdit(): Collection {
		return $this->canEdit;
	}
	
	public function addCanEdit(User $user): self {
		if (! $this->canEdit->contains($user)) {
			$this->canEdit->add($user);
			if (! $user->getEditableProjects()->contains($this)) {
				$user->addEditableProject($this);
			}
		}
		return $this;
	}
	
	public function removeCanEdit(User $user): self {
		if ($this->canEdit->contains($user)) {
			$this->canEdit->removeElement($user);
			if ($user->getEditableProjects()->contains($this)) {
				$user->removeEditableProject($this);
			}
		}
		return $this;
	}
}
