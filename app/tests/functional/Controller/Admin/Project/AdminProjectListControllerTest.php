<?php

declare(strict_types = 1);

namespace App\Tests\functional\Controller\Admin\Project;

use Symfony\Component\HttpFoundation\Request;

class AdminProjectListControllerTest extends \App\Tests\BaseWebTestCase {
	public function testList(): void {
		$admin = $this->getOneAdmin();
		$this->client->loginUser($admin);
		$this->client->request(Request::METHOD_GET, '/admin/projects');
		static::assertResponseIsSuccessful();
	}
}