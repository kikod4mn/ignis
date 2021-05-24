<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Repository\UserRepository;
use App\Security\ConfirmEmailService;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ConfirmEmailRequestFixtures extends BaseFixture implements DependentFixtureInterface {
	public function __construct(private UserRepository $userRepository, private ConfirmEmailService $confirmEmailService) { }
	
	public function loadData(): void {
		$users = $this->userRepository->findAll();
		foreach ($users as $user) {
			$confirmToken = $this->confirmEmailService->createConfirmRequest(
				$user, $this->getFaker()->ipv4, $this->getFaker()->userAgent
			);
			$confirmToken->generateUuid();
			$confirmToken->setCreationTimestamps();
			$this->manager->persist($confirmToken);
		}
		$this->manager->flush();
	}
	
	public function getDependencies(): array {
		return [UserFixtures::class];
	}
}