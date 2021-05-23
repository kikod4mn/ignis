<?php

declare(strict_types = 1);

namespace App\Tests\Behat;

use App\Entity\Role;
use App\Entity\User;
use App\Service\TimeCreator;
use App\Tests\TestDatabaseHelper;
use Behat\Behat\Context\Context;
use Behat\Mink\Session;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class AuthenticationContext implements Context {
	private static ContainerInterface $container;
	private static Generator          $faker;
	
	public function __construct(
		private KernelInterface $kernel, private EntityManagerInterface $em, private UserPasswordEncoderInterface $encoder,
		private Session $session, private RouterInterface $router
	) {
		self::$container = $this->kernel->getContainer();
		self::$faker     = Factory::create();
	}
	
	/**
	 * @BeforeSuite
	 */
	public static function setup(): void {
		TestDatabaseHelper::reset();
	}
	
	/**
	 * @Given there is an admin user :email with password :password
	 */
	public function thereIsAnAdminUserWithPassword(string $email, string $password): void {
		$role = $this->em->getRepository(Role::class)->findOneBy(['name' => Role::ROLE_ADMIN]);
		if ($role === null) {
			$role = (new Role())->setName(Role::ROLE_ADMIN);
			$role->generateUuid();
			$this->em->persist($role);
			$this->em->flush();
		}
		$user = new User();
		$user
			->setName(self::$faker->name)
			->setEmail($email)
			->setPassword($this->encoder->encodePassword($user, $password))
			->setRoles(new ArrayCollection([$role]))
			->setActive(true)
			->setEmailConfirmedAt(TimeCreator::randomPast())
			->setEmailConfirmToken(null)
			->setCreationTimestamps()
		;
		$user->eraseCredentials();
		$user->generateUuid();
		$this->em->persist($user);
		$this->em->flush();
	}
	
	/**
	 * @Given I am on named route :pageName
	 */
	public function iAmOn(string $pageName): void {
		$this->session->visit($this->router->generate($pageName));
	}
	
	/**
	 * @Given there is a regular user :email with password :password
	 */
	public function thereIsARegularUserWithPassword(string $email, string $password): void {
		$role = $this->em->getRepository(Role::class)->findOneBy(['name' => Role::ROLE_USER]);
		if ($role === null) {
			$role = (new Role())->setName(Role::ROLE_ADMIN);
			$role->generateUuid();
			$this->em->persist($role);
			$this->em->flush();
		}
		$user = new User();
		$user
			->setName(self::$faker->name)
			->setEmail($email)
			->setPassword($this->encoder->encodePassword($user, $password))
			->setRoles(new ArrayCollection([$role]))
			->setActive(true)
			->setEmailConfirmedAt(TimeCreator::randomPast())
			->setEmailConfirmToken(null)
			->setCreationTimestamps()
		;
		$user->eraseCredentials();
		$user->generateUuid();
		$this->em->persist($user);
		$this->em->flush();
	}
	
	/**
	 * @Then dump the page
	 */
	public function dumpThePage(): void {
		/** @noinspection ForgottenDebugOutputInspection */
		dd($this->session->getPage()->getContent());
	}
}