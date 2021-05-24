<?php

declare(strict_types = 1);

namespace App\Tests\Integration\Controller\User\Account;

use App\Entity\ConfirmEmailRequest;
use App\Repository\ConfirmEmailRequestRepository;
use App\Tests\Integration\BaseWebTestCase;
use App\Tests\Integration\IH;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\Controller\User\Account\EmailConfirmController
 */
class EmailConfirmControllerTest extends BaseWebTestCase {
	public function testEmailConfirmation(): void {
		/** @var ConfirmEmailRequestRepository $confirmEmailRequestRepository */
		$confirmEmailRequestRepository = IH::getRepository(static::$container, ConfirmEmailRequestRepository::class);
		$confirmRequests               = array_filter(
			$confirmEmailRequestRepository->findAll(),
			static fn (ConfirmEmailRequest $c) => $c->getExpiresAt()->getTimestamp() > time()
		);
		$confirmRequest                = $confirmRequests[array_rand($confirmRequests)];
		$route                         = sprintf('/credentials/confirm/email/%s', $confirmRequest->getHashedToken());
		$this->getClient()->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
		static::assertStringContainsStringIgnoringCase(
			'Awesome! Your email has been confirmed! After an admin activates your account, you will have full API access!',
			(string) $this->getClient()->getResponse()->getContent()
		);
	}
}