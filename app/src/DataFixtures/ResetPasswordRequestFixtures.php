<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\ResetPasswordRequest;
use App\Security\ResetPasswordService;
use Carbon\Carbon;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ResetPasswordRequestFixtures extends BaseFixture implements DependentFixtureInterface {
	public function __construct(private ResetPasswordService $resetPasswordService) { }
	
	public function loadData(): void {
		for ($i = 0; $i < 21; $i++) {
			$request = $this->resetPasswordService->createResetRequest(
				$this->getUser(), $this->getFaker()->ipv4, $this->getFaker()->userAgent
			);
			$request->setCreationTimestamps();
			$request->generateUuid();
			$this->manager->persist($request);
		}
		$this->manager->flush();
	}
	
	public function getDependencies(): array {
		return [UserFixtures::class];
	}
}