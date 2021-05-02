<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Feature;
use App\Entity\Language;
use App\Entity\Project;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Factory;
use Faker\Generator;
use function array_key_exists;
use function count;
use function random_int;
use function sprintf;
use function str_starts_with;

/**
 * @codeCoverageIgnore
 */
abstract class BaseFixture extends Fixture implements FixtureInterface {
	/** @var array<string, array<int, string>> */
	protected array         $references = [];
	protected ObjectManager $manager;
	protected Generator     $faker;
	
	abstract public function loadData(): void;
	
	public function load(ObjectManager $manager): void {
		$this->manager = $manager;
		$this->loadData();
	}
	
	protected function createMany(string $className, int $count, callable $factory): void {
		for ($i = 0; $i < $count; $i++) {
			$entity = new $className($className);
			$factory($entity, $i);
			$this->manager->persist($entity);
			$this->setReference(sprintf('%s_%d', $className, $i), $entity);
		}
		$this->manager->flush();
	}
	
	/** @throws Exception */
	protected function getRandomRef(string $className): object {
		// First we check if our references contain the className we need.
		if (! array_key_exists($className, $this->references)) {
			// If the class does not exist in the ref index, we will load them from the reference repo.
			$this->populateRefIndex($className);
		}
		try {
			return $this->getReference($this->references[$className][random_int(0, count($this->references[$className]) - 1)]);
		} catch (Exception $e) {
			// throw for now... golden forever temporary
			throw new $e(sprintf('Cannot find any references for class "%s"', $className));
		}
	}
	
	protected function populateRefIndex(string $className): void {
		$this->references[$className] = [];
		foreach ($this->referenceRepository->getReferences() as $key => $ref) {
			if (str_starts_with($key, sprintf('%s_', $className))) {
				$this->references[$className][] = $key;
			}
		}
	}
	
	protected function getFaker(): Generator {
		if (! isset($this->faker)) {
			$this->faker = Factory::create();
		}
		return $this->faker;
	}
	
	protected function getProject(): Project {
		return $this->getRandomRef(Project::class);
	}
	
	protected function getProjectLead(): User {
		/** @var User $user */
		$user = $this->getRandomRef(User::class);
		if (! $user->hasRole(Role::ROLE_PROJECT_LEAD)) {
			return $this->getUser();
		}
		return $user;
	}
	
	protected function getUser(): User {
		/** @var User $user */
		$user = $this->getRandomRef(User::class);
		if ($user->hasRole(Role::ROLE_PROJECT_LEAD)
			|| $user->hasRole(Role::ROLE_ADMIN)
			|| ! $user->getActive()
		) {
			return $this->getUser();
		}
		return $user;
	}
	
	protected function getRole(string $role): Role {
		/** @var Role $foundRole */
		$foundRole = $this->getRandomRef(Role::class);
		if ($role === $foundRole->getName()) {
			return $foundRole;
		}
		return $this->getRole($role);
	}
	
	protected function getCategory(): Category {
		return $this->getRandomRef(Category::class);
	}
	
	protected function getFeature(): Feature {
		return $this->getRandomRef(Feature::class);
	}
	
	protected function getLanguage(): Language {
		return $this->getRandomRef(Language::class);
	}
}