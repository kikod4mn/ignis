<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Concerns\IdConcern;
use App\Entity\Concerns\SoftDeleteConcern;
use App\Entity\Concerns\TimestampsConcern;
use App\Entity\Contracts\SoftDeleteContract;
use App\Entity\Contracts\VersionableContract;
use App\Entity\Contracts\IdContract;
use App\Entity\Contracts\TimeStampableContract;
use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="Email is already taken. Please use the forgot password link.")
 */
class User implements IdContract, UserInterface, TimeStampableContract, Stringable, VersionableContract, SoftDeleteContract {
	use SoftDeleteConcern;
	use TimestampsConcern;
	use IdConcern;
	
	public const ROLE_TEST_USER         = 'ROLE_TEST_USER';
	public const ROLE_USER              = 'ROLE_USER';
	public const ROLE_EDIT_ACCOUNT      = 'ROLE_EDIT_ACCOUNT';
	public const ROLE_EDIT_PROFILE      = 'ROLE_EDIT_PROFILE';
	public const ROLE_PROJECT_LEAD      = 'ROLE_PROJECT_LEAD';
	public const ROLE_ADMIN             = 'ROLE_ADMIN';
	public const ROLE_DELETE_USER       = 'ROLE_DELETE_USER';
	public const ROLE_MODIFY_ROLES      = 'ROLE_MODIFY_ROLES';
	public const ROLE_VIEW_PROJECT      = 'ROLE_VIEW_PROJECT';
	public const ROLE_ADD_PROJECT       = 'ROLE_ADD_PROJECT';
	public const ROLE_EDIT_PROJECT      = 'ROLE_EDIT_PROJECT';
	public const ROLE_DELETE_PROJECT    = 'ROLE_DELETE_PROJECT';
	public const ROLE_ADD_FEATURE       = 'ROLE_ADD_FEATURE';
	public const ROLE_IMPLEMENT_FEATURE = 'ROLE_IMPLEMENT_FEATURE';
	public const ROLE_EDIT_FEATURE      = 'ROLE_EDIT_FEATURE';
	public const ROLE_DELETE_FEATURE    = 'ROLE_DELETE_FEATURE';
	public const ROLE_ADD_CATEGORY      = 'ROLE_ADD_CATEGORY';
	public const ROLE_EDIT_CATEGORY     = 'ROLE_EDIT_CATEGORY';
	public const ROLE_DELETE_CATEGORY   = 'ROLE_DELETE_CATEGORY';
	public const ROLE_ADD_LANGUAGE      = 'ROLE_ADD_LANGUAGE';
	public const ROLE_EDIT_LANGUAGE     = 'ROLE_EDIT_LANGUAGE';
	public const ROLE_DELETE_LANGUAGE   = 'ROLE_DELETE_LANGUAGE';
	public const ROLE_ADD_BUG           = 'ROLE_ADD_BUG';
	public const ROLE_EDIT_BUG          = 'ROLE_EDIT_BUG';
	public const ROLE_FIX_BUG           = 'ROLE_FIX_BUG';
	public const ROLE_DELETE_BUG        = 'ROLE_DELETE_BUG';
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	private ?string $name = null;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=false, unique=true)
	 */
	private ?string $email = null;
	
	/**
	 * @ORM\Column(type="array", nullable=false)
	 * @var array<int, string>
	 */
	private array $oldEmails = [];
	
	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\Image", inversedBy="userAvatar")
	 */
	private ?Image $avatar = null;
	
	/**
	 * @ORM\Column(type="string", length=8096, nullable=false)
	 */
	private ?string $password = null;
	
	/**
	 * @ORM\Column(type="array", nullable=false)
	 * @var array<int, string>
	 */
	private array $oldPasswordHashes = [];
	
	/**
	 * @var null|string
	 */
	private ?string $plainPassword = null;
	
	/**
	 * @ORM\Column(type="boolean", options={"default":0})
	 */
	private bool $active = false;
	
	/**
	 * NOTE this is the admin user disabled functionality
	 * @ORM\Column(type="boolean", options={"default":0})
	 */
	private bool $disabled = false;
	
	/**
	 * @ORM\Column(type="carbon_immutable", nullable=true)
	 */
	private ?DateTimeInterface $agreedToTermsAt = null;
	
	// todo extract login events for more history
	/**
	 * @ORM\Column(type="carbon_immutable", nullable=true)
	 */
	private ?DateTimeInterface $lastLoginAt = null;
	
	/**
	 * @ORM\Column(type="string", length=64, nullable=true)
	 */
	private ?string $lastLoginFromIp = null;
	
	/**
	 * @ORM\Column(type="string", length=300, nullable=true)
	 */
	private ?string $lastLoginFromBrowser = null;
	
	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\ConfirmEmailRequest")
	 */
	private ?ConfirmEmailRequest $confirmEmailRequest = null;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Project", mappedBy="author")
	 * @var Collection<int, Project>
	 */
	private Collection $projects;
	
	/**
	 * @ORM\ManyToMany(targetEntity="Project", mappedBy="canEdit")
	 * @var Collection<int, Project>
	 */
	private Collection $editableProjects;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Project", mappedBy="canView")
	 * @var Collection<int, Project>
	 */
	private Collection $viewableProjects;
	
	/**
	 * @ORM\OneToMany(targetEntity="Bug", mappedBy="author")
	 * @var Collection<int, Bug>
	 */
	private Collection $bugs;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Feature", mappedBy="author")
	 * @var Collection<int, Feature>
	 */
	private Collection $features;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Image", mappedBy="author")
	 * @var Collection<int, Image>
	 */
	private Collection $images;
	
	/**
	 * @ORM\Column(type="array")
	 * @var array<int, string>
	 */
	private array $roles;
	
	public function __construct() {
		$this->projects         = new ArrayCollection();
		$this->bugs             = new ArrayCollection();
		$this->features         = new ArrayCollection();
		$this->images           = new ArrayCollection();
		$this->roles            = [];
		$this->editableProjects = new ArrayCollection();
		$this->viewableProjects = new ArrayCollection();
	}
	
	public function getName(): ?string {
		return $this->name;
	}
	
	public function setName(string $name): User {
		$this->name = $name;
		return $this;
	}
	
	public function getUsername(): string {
		return (string) $this->email;
	}
	
	public function getEmail(): ?string {
		return $this->email;
	}
	
	public function setEmail(string $email): User {
		$this->email = $email;
		return $this;
	}
	
	/**
	 * @return array<int, string>
	 */
	public function getOldEmails(): array {
		return $this->oldEmails;
	}
	
	/**
	 * @param   array<int, string>   $oldEmails
	 * @return User
	 */
	public function setOldEmails(array $oldEmails): User {
		$this->oldEmails = $oldEmails;
		return $this;
	}
	
	public function addOldEmail(string $email): User {
		$this->oldEmails[] = $email;
		return $this;
	}
	
	public function displayAvatar(): string {
		if ($this->avatar === null) {
			return 'build/images/_defaults/_default_avatar.png';
		}
		return (string) $this->avatar->getPath();
	}
	
	public function getAvatar(): ?Image {
		return $this->avatar;
	}
	
	public function setAvatar(?Image $avatar): User {
		$this->avatar = $avatar;
		return $this;
	}
	
	public function getPassword(): ?string {
		return $this->password;
	}
	
	public function setPassword(string $password): User {
		$this->password = $password;
		return $this;
	}
	
	/**
	 * @return array<int, string>
	 */
	public function getOldPasswordHashes(): array {
		return $this->oldPasswordHashes;
	}
	
	/**
	 * @param   array<int, string>   $oldPasswordHashes
	 * @return $this
	 */
	public function setOldPasswordHashes(array $oldPasswordHashes): User {
		$this->oldPasswordHashes = $oldPasswordHashes;
		return $this;
	}
	
	public function addOldPasswordHash(string $hash): User {
		$this->oldPasswordHashes[] = $hash;
		return $this;
	}
	
	public function getPlainPassword(): ?string {
		return $this->plainPassword;
	}
	
	public function setPlainPassword(?string $plainPassword): User {
		$this->plainPassword = $plainPassword;
		return $this;
	}
	
	public function getActive(): bool {
		return $this->active;
	}
	
	public function setActive(bool $active): User {
		$this->active = $active;
		return $this;
	}
	
	public function getDisabled(): bool {
		return $this->disabled;
	}
	
	public function setDisabled(bool $disabled): User {
		$this->disabled = $disabled;
		return $this;
	}
	
	public function getAgreedToTermsAt(): ?DateTimeInterface {
		return $this->agreedToTermsAt;
	}
	
	public function setAgreedToTermsAt(?DateTimeInterface $agreedToTermsAt): User {
		$this->agreedToTermsAt = $agreedToTermsAt;
		return $this;
	}
	
	public function getLastLoginAt(): ?DateTimeInterface {
		return $this->lastLoginAt;
	}
	
	public function setLastLoginAt(DateTimeInterface $lastLoginAt): User {
		$this->lastLoginAt = $lastLoginAt;
		return $this;
	}
	
	public function getLastLoginFromIp(): ?string {
		return $this->lastLoginFromIp;
	}
	
	public function setLastLoginFromIp(?string $lastLoginFromIp): User {
		$this->lastLoginFromIp = $lastLoginFromIp;
		return $this;
	}
	
	public function getLastLoginFromBrowser(): ?string {
		return $this->lastLoginFromBrowser;
	}
	
	public function setLastLoginFromBrowser(?string $lastLoginFromBrowser): User {
		$this->lastLoginFromBrowser = $lastLoginFromBrowser;
		return $this;
	}
	
	public function getConfirmEmailRequest(): ?ConfirmEmailRequest {
		return $this->confirmEmailRequest;
	}
	
	public function setConfirmEmailRequest(ConfirmEmailRequest $confirmEmailRequest): void {
		$this->confirmEmailRequest = $confirmEmailRequest;
	}
	
	public function eraseCredentials(): void {
		$this->plainPassword = null;
	}
	
	public function getSalt(): ?string {
		return null;
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
			$project->setAuthor($this);
		}
		return $this;
	}
	
	public function removeProject(Project $project): self {
		if ($this->projects->removeElement($project) && $project->getAuthor() === $this) {
			$project->setAuthor(null);
		}
		return $this;
	}
	
	/**
	 * @return Collection<int, Project>
	 */
	public function getViewableProjects(): Collection {
		return $this->viewableProjects;
	}
	
	public function addViewableProject(Project $project): self {
		if (! $this->viewableProjects->contains($project)) {
			$this->viewableProjects->add($project);
			if (! $project->getCanView()->contains($this)) {
				$project->addCanView($this);
			}
		}
		return $this;
	}
	
	public function removeViewableProject(Project $project): self {
		if ($this->viewableProjects->contains($project)) {
			$this->viewableProjects->removeElement($project);
			if ($project->getCanView()->contains($this)) {
				$project->removeCanView($this);
			}
		}
		return $this;
	}
	
	/**
	 * @return Collection<int, Project>
	 */
	public function getEditableProjects(): Collection {
		return $this->editableProjects;
	}
	
	public function addEditableProject(Project $project): self {
		if (! $this->editableProjects->contains($project)) {
			$this->editableProjects->add($project);
			if (! $project->getCanEdit()->contains($this)) {
				$project->addCanEdit($this);
			}
		}
		return $this;
	}
	
	public function removeEditableProject(Project $project): self {
		if ($this->editableProjects->contains($project)) {
			$this->editableProjects->removeElement($project);
			if ($project->getCanEdit()->contains($this)) {
				$project->removeCanEdit($this);
			}
		}
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
			$bug->setAuthor($this);
		}
		return $this;
	}
	
	public function removeBug(Bug $bug): self {
		if ($this->bugs->removeElement($bug) && $bug->getAuthor() === $this) {
			$bug->setAuthor(null);
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
			$feature->setAuthor($this);
		}
		return $this;
	}
	
	public function removeFeature(Feature $feature): self {
		if ($this->features->removeElement($feature) && $feature->getAuthor() === $this) {
			$feature->setAuthor(null);
		}
		return $this;
	}
	
	/**
	 * @return Collection<int, Image>
	 */
	public function getImages(): Collection {
		return $this->images;
	}
	
	/**
	 * @param   array<int, Image>   $images
	 * @return $this
	 */
	public function setImages(array $images): self {
		$this->images = new ArrayCollection($images);
		return $this;
	}
	
	public function addImage(Image $image): self {
		if (! $this->images->contains($image)) {
			$this->images->add($image);
			$image->setAuthor($this);
		}
		return $this;
	}
	
	public function removeImage(Image $image): self {
		if ($this->images->removeElement($image) && $image->getAuthor() === $this) {
			$image->setAuthor(null);
		}
		return $this;
	}
	
	public function getRoles(): array {
		return array_unique([...$this->roles, self::ROLE_USER]);
	}
	
	public function hasRole(string $role): bool {
		return in_array($role, $this->roles, true);
	}
	
	/**
	 * @param   array<int, string>   $roles
	 */
	public function setRoles(array $roles): User {
		$this->roles = $roles;
		return $this;
	}
	
	public function addRole(string $role): User {
		$this->roles = array_unique([...$this->roles, $role]);
		return $this;
	}
	
	public function removeRole(string $role): User {
		$this->roles = array_filter($this->roles, static fn (string $r) => $r !== $role);
		return $this;
	}
	
	public function __toString(): string {
		return sprintf(
			'User: { name: "%s",%s id: "%s",%s uuid: "%s",%s roles: "%s" }%s',
			$this->getName(), PHP_EOL,
			$this->getId(), PHP_EOL,
			(string) $this->getUuid(), PHP_EOL,
			implode(', ', $this->getRoles()), PHP_EOL
		);
	}
}
