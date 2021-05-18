<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Service\TimeCreator;
use App\Service\TokenGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use function mt_rand;

class UserFixtures extends BaseFixture implements DependentFixtureInterface {
	public const PASSWORD      = 'secret';
	public const PASSWORD_HASH = '$argon2id$v=19$m=65536,t=4,p=1$bXIzMzM2dEdBRnF2dTBmZw$TPURFO0mxfivx7d3M3W0el2TFw2HpdYloxUEsdc7nIo';
	protected Generator $faker;
	
	public function __construct(private TokenGenerator $tokenGenerator, private RoleRepository $roleRepository, private UserPasswordEncoderInterface $encoder) {
		$this->faker = Factory::create();
	}
	
	public function loadData(): void {
		// Not yet activated with emails not verified
		$this->createMany(
			User::class, 4, function (User $user): void {
			$this->setBasicUserRoles($user);
			$this->setBasicUserProps($user);
			$user->setActive(false);
			$user->setEmailConfirmToken($this->tokenGenerator->alphanumericToken(64));
		}
		);
		// Not yet activated with emails verified
		$this->createMany(
			User::class, 4, function (User $user): void {
			$this->setBasicUserRoles($user);
			$this->setBasicUserProps($user);
			$user->setActive(false);
			$user->setEmailConfirmedAt(TimeCreator::randomPast());
			$user->setEmailConfirmToken(null);
		}
		);
		// Active users with emails verified
		$this->createMany(
			User::class, 65, function (User $user): void {
			$this->setBasicUserRoles($user);
			$this->setBasicUserProps($user);
			$this->setUserActive($user);
			$this->setUserLastLogin($user);
			/** @noinspection RandomApiMigrationInspection */
			$user->setUpdatedAt(mt_rand(0, 1) > 0 ? TimeCreator::randomPast() : null);
		}
		);
		// Active and password reset requested
		$this->createMany(
			User::class, 4, function (User $user): void {
			$this->setBasicUserRoles($user);
			$this->setBasicUserProps($user);
			$this->setUserActive($user);
			$this->setPasswordReset($user);
		}
		);
		// Admins
		$this->createMany(
			User::class, 5, function (User $user): void {
			$this->setAdminUserRoles($user);
			$this->setBasicUserProps($user);
			$this->setUserActive($user);
		}
		);
		// Project leads
		$this->createMany(
			User::class, 15, function (User $user): void {
			$this->setProjectLeadRoles($user);
			$this->setBasicUserProps($user);
			$this->setUserActive($user);
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
			/** @var Collection<int, Role> $roles */
			$roles = new ArrayCollection(
				[
					$this->roleRepository->findOneBy(['name' => Role::ROLE_TEST_USER]),
				]
			);
			$user->setRoles($roles);
			$user->setActive(true);
			$user->setEmailConfirmedAt(TimeCreator::now());
			$user->setEmailConfirmToken(null);
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
			/** @var Collection<int, Role> $roles */
			$roles = new ArrayCollection(
				[
					$this->roleRepository->findOneBy(['name' => Role::ROLE_ADMIN]),
				]
			);
			$user->setRoles($roles);
			$user->setActive(true);
			$user->setEmailConfirmedAt(TimeCreator::now());
			$user->setEmailConfirmToken(null);
		}
		);
	}
	
	/**
	 * @return array<class-string<FixtureInterface>>
	 */
	public function getDependencies(): array {
		return [RoleFixtures::class];
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
		/** @var Collection<int, Role> $roles */
		$roles = new ArrayCollection(
			[
				$this->roleRepository->findOneBy(['name' => Role::ROLE_USER]),
			]
		);
		$user->setRoles($roles);
	}
	
	protected function setAdminUserRoles(User $user): void {
		/** @var Collection<int, Role> $roles */
		$roles = new ArrayCollection(
			[
				$this->roleRepository->findOneBy(['name' => Role::ROLE_ADMIN]),
			]
		);
		$user->setRoles($roles);
	}
	
	protected function setProjectLeadRoles(User $user): void {
		/** @var Collection<int, Role> $roles */
		$roles = new ArrayCollection(
			[
				$this->roleRepository->findOneBy(['name' => Role::ROLE_PROJECT_LEAD]),
			]
		);
		$user->setRoles($roles);
	}
	
	protected function setUserActive(User $user): void {
		$user->setActive(true);
		$user->setEmailConfirmedAt(TimeCreator::randomPast());
		$user->setEmailConfirmToken(null);
	}
	
	protected function setUserLastLogin(User $user): void {
		$user->setLastLoginAt(TimeCreator::randomPast());
		$user->setLastLoginFromIp($this->faker->ipv4);
		$user->setLastLoginFromBrowser($this->faker->userAgent);
	}
	
	protected function setPasswordReset(User $user): void {
		$user->setPasswordResetToken($this->tokenGenerator->alphanumericToken(64));
		$user->setPasswordResetTokenRequestedAt(TimeCreator::randomPast());
		$user->setPasswordResetTokenRequestedFromIp($this->faker->ipv4);
		$user->setPasswordResetTokenRequestedFromBrowser($this->faker->userAgent);
	}
}