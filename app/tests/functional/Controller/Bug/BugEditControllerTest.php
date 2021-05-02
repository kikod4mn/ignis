<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Bug;

use App\Entity\User;
use App\Repository\BugRepository;
use App\Tests\BaseWebTestCase;
use App\Tests\functional\Concerns\TokenManagerConcern;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

class BugEditControllerTest extends BaseWebTestCase {
	use TokenManagerConcern;
	
	public function testBugEditPage(): void {
		$project = $this->getOneProject();
		$bug     = $this->getOneBug();
		$route   = sprintf('/projects/%s/bugs/%s/edit', $project->getUuid(), $bug->getUuid());
		$this->client->loginUser($this->getOneProjectLead());
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testBugEditFormSubmit(): void {
		$newTitle       = $this->getFaker()->sentence();
		$newDescription = $this->getFaker()->realText(600);
		$bug            = $this->getOneBug();
		$project        = $bug->getProject();
		$author = $this->getOneProjectLead();
		$this->client->loginUser($author);
		$route = sprintf('/projects/%s/bugs/%s/edit', $project?->getUuid(), $bug->getUuid());
		$this->client->request(
			Request::METHOD_POST,
			$route,
			[
				'bug_edit' => [
					'_title'       => $newTitle,
					'_description' => $newDescription,
					'_token'       => $this->getTokenManager()->getToken('_bug_edit[_csrf_token]'),
				],
			]
		);
		/** @var BugRepository $bugRepository */
		$bugRepository = $this->getRepository(BugRepository::class);
		$bug           = $bugRepository->findOneBy(['uuid' => $bug->getUuid()]);
		static::assertTrue($bug?->getTitle() === $newTitle);
		static::assertTrue($bug?->getDescription() === $newDescription);
	}
	
	public function testBugEditFormForTestUser(): void {
		$newTitle       = $this->getFaker()->sentence();
		$newDescription = $this->getFaker()->realText(600);
		$bug            = $this->getOneBug();
		$project        = $bug->getProject();
		$user           = $this->getTestUser();
		$this->client->loginUser($user);
		$route = sprintf('/projects/%s/bugs/%s/edit', $project?->getUuid(), $bug->getUuid());
		$this->client->request(
			Request::METHOD_POST,
			$route,
			[
				'bug_edit' => [
					'_title'       => $newTitle,
					'_description' => $newDescription,
					'_token'       => $this->getTokenManager()->getToken('_bug_edit[_csrf_token]'),
				],
			]
		);
		/** @var BugRepository $bugRepository */
		$bugRepository = $this->getRepository(BugRepository::class);
		$bug           = $bugRepository->findOneBy(['uuid' => $bug->getUuid()]);
		static::assertTrue($bug?->getTitle() !== $newTitle);
		static::assertTrue($bug?->getDescription() !== $newDescription);
	}
}