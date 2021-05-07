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
use function count;
use function implode;
use const PHP_EOL;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="Email is already taken. Please use the forgot password link.")
 */
class User implements IdContract, UserInterface, TimeStampableContract, Stringable, VersionableContract, SoftDeleteContract {
	use SoftDeleteConcern;
	use TimestampsConcern;
	use IdConcern;
	
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
	 * @ORM\Column(type="string", length=64, nullable=true)
	 */
	private ?string $passwordResetToken = null;
	
	/**
	 * @ORM\Column(type="carbon_immutable", nullable=true)
	 */
	private ?DateTimeInterface $passwordResetTokenRequestedAt = null;
	
	/**
	 * @ORM\Column(type="string", length=64, nullable=true)
	 */
	private ?string $passwordResetTokenRequestedFromIp = null;
	
	/**
	 * @ORM\Column(type="string", length=300, nullable=true)
	 */
	private ?string $passwordResetTokenRequestedFromBrowser = null;
	
	/**
	 * @ORM\Column(type="string", length=64, nullable=true)
	 */
	private ?string $emailConfirmToken = null;
	
	/**
	 * @ORM\Column(type="carbon_immutable", nullable=true)
	 */
	private ?DateTimeInterface $emailConfirmedAt = null;
	
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
	 * @ORM\ManyToMany(targetEntity="App\Entity\Role", inversedBy="users")
	 * @var Collection<int, Role>
	 */
	private Collection $roles;
	
	/**
	 * @var array<int, string>
	 */
	private array $roleCache = [];
	
	public function __construct() {
		$this->projects         = new ArrayCollection();
		$this->bugs             = new ArrayCollection();
		$this->features         = new ArrayCollection();
		$this->images           = new ArrayCollection();
		$this->roles            = new ArrayCollection();
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
	
	public function getUsername(): ?string {
		return $this->email;
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
		if (! $this->avatar) {
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
	
	public function getPasswordResetToken(): ?string {
		return $this->passwordResetToken;
	}
	
	public function setPasswordResetToken(?string $passwordResetToken): User {
		$this->passwordResetToken = $passwordResetToken;
		return $this;
	}
	
	public function getPasswordResetTokenRequestedAt(): ?DateTimeInterface {
		return $this->passwordResetTokenRequestedAt;
	}
	
	public function setPasswordResetTokenRequestedAt(?DateTimeInterface $requestedAt): User {
		$this->passwordResetTokenRequestedAt = $requestedAt;
		return $this;
	}
	
	public function getPasswordResetTokenRequestedFromIp(): ?string {
		return $this->passwordResetTokenRequestedFromIp;
	}
	
	public function setPasswordResetTokenRequestedFromIp(?string $passwordResetTokenRequestedFromIp): User {
		$this->passwordResetTokenRequestedFromIp = $passwordResetTokenRequestedFromIp;
		return $this;
	}
	
	public function getPasswordResetTokenRequestedFromBrowser(): ?string {
		return $this->passwordResetTokenRequestedFromBrowser;
	}
	
	public function setPasswordResetTokenRequestedFromBrowser(?string $passwordResetTokenRequestedFromBrowser): User {
		$this->passwordResetTokenRequestedFromBrowser = $passwordResetTokenRequestedFromBrowser;
		return $this;
	}
	
	public function getEmailConfirmToken(): ?string {
		return $this->emailConfirmToken;
	}
	
	public function setEmailConfirmToken(?string $emailConfirmToken): User {
		$this->emailConfirmToken = $emailConfirmToken;
		return $this;
	}
	
	public function getEmailConfirmedAt(): ?DateTimeInterface {
		return $this->emailConfirmedAt;
	}
	
	public function setEmailConfirmedAt(?DateTimeInterface $emailConfirmedAt): User {
		$this->emailConfirmedAt = $emailConfirmedAt;
		return $this;
	}
	
	public function getActive(): ?bool {
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
		if ($this->projects->removeElement($project)) {
			if ($project->getAuthor() === $this) {
				$project->setAuthor(null);
			}
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
		if ($this->bugs->removeElement($bug)) {
			if ($bug->getAuthor() === $this) {
				$bug->setAuthor(null);
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
			$feature->setAuthor($this);
		}
		return $this;
	}
	
	public function removeFeature(Feature $feature): self {
		if ($this->features->removeElement($feature)) {
			if ($feature->getAuthor() === $this) {
				$feature->setAuthor(null);
			}
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
		if ($this->images->removeElement($image)) {
			if ($image->getAuthor() === $this) {
				$image->setAuthor(null);
			}
		}
		return $this;
	}
	
	/**
	 * @return array<int, string>
	 */
	public function getRoles(): array {
		if (count($this->roleCache) === 0) {
			foreach ($this->roles->toArray() as $role) {
				$this->roleCache[] = (string) $role->getName();
			}
		}
		if (count($this->roleCache) === 0) {
			return [Role::ROLE_USER];
		}
		return [...$this->roleCache];
	}
	
	public function hasRole(string $roleToTest): bool {
		if ($this->roles->count() === 0 && $roleToTest === Role::ROLE_USER) {
			return true;
		}
		return $this->roles->exists(
			fn (int $key, Role $role) => $role->getName() === $roleToTest
		);
	}
	
	/**
	 * @param   Collection<int, Role>   $roles
	 * @return $this
	 */
	public function setRoles(Collection $roles): User {
		$this->roles = $roles;
		return $this;
	}
	
	public function addRole(Role $role): User {
		if (! $this->roles->contains($role)) {
			$this->roles->add($role);
		}
		return $this;
	}
	
	public function removeRole(Role $role): User {
		if ($this->roles->contains($role)) {
			$this->roles->removeElement($role);
		}
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
