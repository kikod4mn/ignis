<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Concerns\EntityIdConcern;
use App\Entity\Contracts\EntityIdContract;
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
class Role implements EntityIdContract, Stringable {
	use EntityIdConcern;
	
	const ROLE_TEST_USER         = 'ROLE_TEST_USER';
	const ROLE_USER              = 'ROLE_USER';
	const ROLE_EDIT_ACCOUNT      = 'ROLE_EDIT_ACCOUNT';
	const ROLE_EDIT_PROFILE      = 'ROLE_EDIT_PROFILE';
	const ROLE_PROJECT_LEAD      = 'ROLE_PROJECT_LEAD';
	const ROLE_ADMIN             = 'ROLE_ADMIN';
	const ROLE_DELETE_USER       = 'ROLE_DELETE_USER';
	const ROLE_MODIFY_ROLES      = 'ROLE_MODIFY_ROLES';
	const ROLE_VIEW_PROJECT      = 'ROLE_VIEW_PROJECT';
	const ROLE_ADD_PROJECT       = 'ROLE_ADD_PROJECT';
	const ROLE_EDIT_PROJECT      = 'ROLE_EDIT_PROJECT';
	const ROLE_DELETE_PROJECT    = 'ROLE_DELETE_PROJECT';
	const ROLE_ADD_FEATURE       = 'ROLE_ADD_FEATURE';
	const ROLE_IMPLEMENT_FEATURE = 'ROLE_IMPLEMENT_FEATURE';
	const ROLE_EDIT_FEATURE      = 'ROLE_EDIT_FEATURE';
	const ROLE_DELETE_FEATURE    = 'ROLE_DELETE_FEATURE';
	const ROLE_ADD_CATEGORY      = 'ROLE_ADD_CATEGORY';
	const ROLE_EDIT_CATEGORY     = 'ROLE_EDIT_CATEGORY';
	const ROLE_DELETE_CATEGORY   = 'ROLE_DELETE_CATEGORY';
	const ROLE_ADD_LANGUAGE      = 'ROLE_ADD_LANGUAGE';
	const ROLE_EDIT_LANGUAGE     = 'ROLE_EDIT_LANGUAGE';
	const ROLE_DELETE_LANGUAGE   = 'ROLE_DELETE_LANGUAGE';
	const ROLE_ADD_BUG           = 'ROLE_ADD_BUG';
	const ROLE_EDIT_BUG          = 'ROLE_EDIT_BUG';
	const ROLE_FIX_BUG           = 'ROLE_FIX_BUG';
	const ROLE_DELETE_BUG        = 'ROLE_DELETE_BUG';
	const ROLES                  = [
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
