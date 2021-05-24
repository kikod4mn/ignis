<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Service\TimeCreator;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends BaseFixture {
	public const PASSWORD      = 'secret';
	public const PASSWORD_HASH = '$argon2id$v=19$m=65536,t=4,p=1$bXIzMzM2dEdBRnF2dTBmZw$TPURFO0mxfivx7d3M3W0el2TFw2HpdYloxUEsdc7nIo';
	protected Generator $faker;
	
	public function __construct(private UserPasswordEncoderInterface $encoder) {
		$this->faker = Factory::create();
	}
	
	public function loadData(): void {
		// Not yet activated with emails not verified
		$this->createMany(
			User::class, 4, function (User $user): void {
			$this->setBasicUserRoles($user);
			$this->setBasicUserProps($user);
		}
		);
		// Not yet activated with emails verified
		$this->createMany(
			User::class, 4, function (User $user): void {
			$this->setBasicUserRoles($user);
			$this->setBasicUserProps($user);
			$user->setActive(false);
		}
		);
		// Active users with emails verified
		$this->createMany(
			User::class, 65, function (User $user): void {
			$this->setBasicUserRoles($user);
			$this->setBasicUserProps($user);
			$this->setUserActive($user);
			/** @noinspection RandomApiMigrationInspection */
			$user->setUpdatedAt(mt_rand(0, 1) > 0 ? TimeCreator::randomPast() : null);
		}
		);
		// Admins
		$this->createMany(
			User::class, 5, function (User $user): void {
			$this->setBasicUserProps($user);
			$this->setUserActive($user);
			$this->setAdminUserRoles($user);
		}
		);
		// Project leads
		$this->createMany(
			User::class, 15, function (User $user): void {
			$this->setBasicUserProps($user);
			$this->setUserActive($user);
			$this->setProjectLeadRoles($user);
		}
		);
		// Test User
		$this->createMany(
			User::class, 1, function (User $user): void {
			$user->generateUuid();
			$user->setName('Test User');
			$user->setEmail('not@test.com');
			$user->setPassword($this->encoder->encodePassword($user, 'test123'));
			$user->setAgreedToTermsAt(TimeCreator::now());
			$user->setCreatedAt(TimeCreator::now());
			$user->setRoles([User::ROLE_TEST_USER]);
			$user->setActive(true);
		}
		);
		// Special Admin User
		$this->createMany(
			User::class, 1, function (User $user): void {
			$user->generateUuid();
			$user->setName('Kristo Leas');
			$user->setEmail('kristo@ignis.ee');
			$user->setPassword($this->encoder->encodePassword($user, 'secret'));
			$user->setAgreedToTermsAt(TimeCreator::now());
			$user->setCreatedAt(TimeCreator::now());
			$user->setRoles([User::ROLE_ADMIN]);
			$user->setActive(true);
		}
		);
	}
	
	protected function setBasicUserProps(User $user): void {
		$user->generateUuid();
		$user->setName($this->faker->unique()->name);
		$user->setEmail($this->faker->unique()->email);
		$user->setPassword(self::PASSWORD_HASH);
		$user->setAgreedToTermsAt(TimeCreator::randomPast());
		$user->setCreatedAt($user->getAgreedToTermsAt() ?? TimeCreator::randomPast());
		
	}
	
	protected function setBasicUserRoles(User $user): void {
		$user->setRoles([User::ROLE_USER]);
	}
	
	protected function setAdminUserRoles(User $user): void {
		$user->setRoles([User::ROLE_ADMIN]);
	}
	
	protected function setProjectLeadRoles(User $user): void {
		$user->setRoles([User::ROLE_PROJECT_LEAD]);
	}
	
	protected function setUserActive(User $user): void {
		$user->setActive(true);
	}
}