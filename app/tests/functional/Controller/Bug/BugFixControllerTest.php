<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Bug;

use App\Entity\Bug;
use App\Entity\Project;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\BugRepository;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use function count;
use function mt_rand;
use function sprintf;

class BugFixControllerTest extends BaseWebTestCase {
	public function testBugFixDoesNotWorkForBugAuthor(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = $this->getRepository(BugRepository::class);
		$bug           = $this->ensureBugAuthorIsNotProjectLead($bugRepository);
		/** @var User $author */
		$author = $bug->getAuthor();
		/** @var Project $project */
		$project = $bug->getProject();
		static::assertFalse($bug->isFixed());
		$route = sprintf('/projects/%s/bugs/%s/fix', $project->getUuid(), $bug->getUuid());
		$this->client->loginUser($author);
		$this->client->request(Request::METHOD_GET, $route);
		static::assertResponseStatusCodeSame(404);
		/** @var Bug $bug */
		$bug = $bugRepository->find($bug->getId());
		static::assertFalse($bug->isFixed());
	}
	
	public function testBugFixWorksForProjectLead(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = $this->getRepository(BugRepository::class);
		/** @var Bug $bug */
		$bug = $bugRepository->findOneBy(['fixed' => false]);
		/** @var Project $project */
		$project = $bug->getProject();
		static::assertFalse($bug->isFixed());
		$route = sprintf('/projects/%s/bugs/%s/fix', $project->getUuid(), $bug->getUuid());
		$this->client->loginUser($this->getOneProjectLead());
		$this->client->request(Request::METHOD_GET, $route);
		$this->client->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var Bug $bug */
		$bug = $bugRepository->find($bug->getId());
		static::assertTrue($bug->isFixed());
	}
	
	public function bugFixForTestUser(): void {
		/** @var BugRepository $bugRepository */
		$bugRepository = $this->getRepository(BugRepository::class);
		/** @var Bug $bug */
		$bug = $bugRepository->findOneBy(['fixed' => false]);
		/** @var Project $project */
		$project = $bug->getProject();
		static::assertFalse($bug->isFixed());
		$route = sprintf('/projects/%s/bugs/%s/fix', $project->getUuid(), $bug->getUuid());
		$this->client->loginUser($this->getTestUser());
		$this->client->request(Request::METHOD_GET, $route);
		$this->client->followRedirect();
		static::assertResponseIsSuccessful();
		/** @var Bug $bug */
		$bug = $bugRepository->find($bug->getId());
		static::assertFalse($bug->isFixed());
	}
	
	private function ensureBugAuthorIsNotProjectLead(BugRepository $bugRepository): Bug {
		$bugs   = $bugRepository->findBy(['fixed' => false]);
		$bug    = $bugs[mt_rand(0, count($bugs) - 1)];
		$author = $bug->getAuthor();
		if ($author?->hasRole(Role::ROLE_PROJECT_LEAD)) {
			return $this->ensureBugAuthorIsNotProjectLead($bugRepository);
		}
		return $bug;
	}
}