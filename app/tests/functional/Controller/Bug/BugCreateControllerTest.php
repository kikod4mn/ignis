<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Bug;

use App\Entity\Bug;
use App\Repository\BugRepository;
use App\Tests\BaseWebTestCase;
use App\Tests\functional\Concerns\TokenManagerConcern;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use function sprintf;

class BugCreateControllerTest extends BaseWebTestCase {
	use TokenManagerConcern;
	
	public function testBugCreatePage(): void {
		$project = $this->getOneProject();
		$user    = $project->getAuthor();
		$route   = sprintf('/projects/%s/bugs/create', $project->getUuid());
		$this->client->loginUser($user);
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseIsSuccessful();
	}
	
	public function testBugCreateFormSubmission(): void {
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->realText(500);
		$project     = $this->getOneProject();
		$user        = $project->getAuthor();
		$route       = sprintf('/projects/%s/bugs/create', $project->getUuid());
		$this->client->loginUser($user);
		$this->client->request(
			Request::METHOD_POST,
			$route,
			[
				'bug_create' => [
					'_title'       => $title,
					'_description' => $description,
					'_token'       => $this->getTokenManager()->getToken('_bug_create[_csrf_token]'),
				],
			]
		);
		/** @var BugRepository $bugRepository */
		$bugRepository = $this->getRepository(BugRepository::class);
		$bug           = $bugRepository->findOneBy(['title' => $title, 'description' => $description]);
		static::assertTrue($bug instanceof Bug);
		/** @var UuidInterface $bugUuid */
		$bugUuid = $bug?->getProject()?->getUuid();
		/** @var UuidInterface $projectUuid */
		$projectUuid = $project->getUuid();
		static::assertTrue($projectUuid->equals($bugUuid));
	}
	
	public function testBugCreateForTestUser(): void {
		$title       = $this->getFaker()->sentence;
		$description = $this->getFaker()->realText(500);
		$user        = $this->getTestUser();
		$project     = $this->getOneProject();
		$route       = sprintf('/projects/%s/bugs/create', $project->getUuid());
		$this->client->loginUser($user);
		$this->client->request(
			Request::METHOD_POST,
			$route,
			[
				'bug_create' => [
					'_title'       => $title,
					'_description' => $description,
					'_token'       => $this->getTokenManager()->getToken('_bug_create[_csrf_token]'),
				],
			]
		);
		static::assertResponseIsSuccessful();
	}
}