<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Concerns\IdConcern;
use App\Entity\Contracts\IdContract;
use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=RoleRepository::class)
 * @UniqueEntity("name")
 */
class Role implements IdContract, Stringable {
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
	public const ROLES                  = [
		self::ROLE_TEST_USER,
		self::ROLE_USER, self::ROLE_ADMIN, self::ROLE_PROJECT_LEAD,
		self::ROLE_VIEW_PROJECT, self::ROLE_ADD_PROJECT, self::ROLE_EDIT_PROJECT, self::ROLE_DELETE_PROJECT,
		self::ROLE_ADD_FEATURE, self::ROLE_EDIT_FEATURE, self::ROLE_DELETE_FEATURE, self::ROLE_IMPLEMENT_FEATURE,
		self::ROLE_ADD_CATEGORY, self::ROLE_EDIT_CATEGORY, self::ROLE_DELETE_CATEGORY,
		self::ROLE_ADD_LANGUAGE, self::ROLE_EDIT_LANGUAGE, self::ROLE_DELETE_LANGUAGE,
		self::ROLE_ADD_BUG, self::ROLE_EDIT_BUG, self::ROLE_FIX_BUG, self::ROLE_DELETE_BUG,
	];
	
	/**
	 * @ORM\Column(type="string", length=255, unique=true, nullable=false)
	 */
	private ?string $name = null;
	
	/**
	 * @ORM\ManyToMany(targetEntity="User", mappedBy="roles")
	 * @var Collection<int, User>
	 */
	private Collection $users;
	
	public function __construct() {
		$this->users = new ArrayCollection();
	}
	
	public function getName(): ?string {
		return $this->name;
	}
	
	public function setName(?string $name): Role {
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return Collection<int, User>
	 */
	public function getUsers(): Collection {
		return $this->users;
	}
	
	public function __toString(): string {
		return (string) $this->name;
	}
}
