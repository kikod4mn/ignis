<?php

declare(strict_types = 1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Bug;
use App\Entity\Project;
use App\Entity\Role;
use App\Entity\User;
use App\Security\Voter\ProjectVoter;
use Doctrine\Common\Collections\Collection;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

class ProjectVoterTest extends TestCase {
	/**
	 * @dataProvider supportsProvider
	 */
	public function testSupports(string $attribute, mixed $subject, bool $expectation): void {
		$security = $this->createMock(Security::class);
		$voter    = new ProjectVoter($security);
		static::assertSame($expectation, $voter->supports($attribute, $subject));
	}
	
	/**
	 * @dataProvider voteProvider
	 */
	public function testVote(
		string $attribute, mixed $subject, TokenInterface $token, Security $security, bool $expectation
	): void {
		$voter = new ProjectVoter($security);
		static::assertSame(
			$expectation,
			$voter->voteOnAttribute($attribute, $subject, $token)
		);
	}
	
	/**
	 * @return Generator<int, array<string, mixed>>
	 */
	public function supportsProvider(): Generator {
		yield [
			'attribute'   => Role::ROLE_PROJECT_LEAD,
			'subject'     => $this->createMock(Project::class),
			'expectation' => true,
		];
		yield [
			'attribute'   => Role::ROLE_VIEW_PROJECT,
			'subject'     => $this->createMock(Project::class),
			'expectation' => true,
		];
		yield [
			'attribute'   => Role::ROLE_ADD_PROJECT,
			'subject'     => $this->createMock(Project::class),
			'expectation' => true,
		];
		yield [
			'attribute'   => Role::ROLE_EDIT_PROJECT,
			'subject'     => $this->createMock(Project::class),
			'expectation' => true,
		];
		yield [
			'attribute'   => Role::ROLE_DELETE_PROJECT,
			'subject'     => $this->createMock(Project::class),
			'expectation' => true,
		];
		yield [
			'attribute'   => Role::ROLE_TEST_USER,
			'subject'     => $this->createMock(Project::class),
			'expectation' => false,
		];
		yield [
			'attribute'   => Role::ROLE_VIEW_PROJECT,
			'subject'     => $this->createMock(Bug::class),
			'expectation' => false,
		];
	}
	
	/**
	 * @return Generator<string, array<string, mixed>>
	 */
	public function voteProvider(): Generator {
		// admin can add new
		yield 'admin can add' => [
			'attribute'   => Role::ROLE_ADD_PROJECT,
			'subject'     => $this->adminProject(),
			'token'       => $this->adminToken(),
			'security'    => $this->adminSecurity(),
			'expectation' => true,
		];
		yield 'admin can edit' => [
			'attribute'   => Role::ROLE_EDIT_PROJECT,
			'subject'     => $this->adminProject(),
			'token'       => $this->adminToken(),
			'security'    => $this->adminSecurity(),
			'expectation' => true,
		];
		yield 'admin can view' => [
			'attribute'   => Role::ROLE_VIEW_PROJECT,
			'subject'     => $this->adminProject(),
			'token'       => $this->adminToken(),
			'security'    => $this->adminSecurity(),
			'expectation' => true,
		];
		yield 'admin can delete' => [
			'attribute'   => Role::ROLE_DELETE_PROJECT,
			'subject'     => $this->adminProject(),
			'token'       => $this->adminToken(),
			'security'    => $this->adminSecurity(),
			'expectation' => true,
		];
		// project lead can add new
		$author = $this->createStub(User::class);
		$author->expects(static::once())
			   ->method('getId')
			   ->willReturn(1)
		;
		$loggedUser = $this->createStub(User::class);
		$loggedUser->expects(static::once())
				   ->method('getId')
				   ->willReturn(2)
		;
		$security = $this->createStub(Security::class);
		$security->method('isGranted')
				 ->withConsecutive([Role::ROLE_ADMIN], [Role::ROLE_ADD_PROJECT])
				 ->willReturnOnConsecutiveCalls(false, true)
		;
		yield 'project lead can add new' => [
			'attribute'   => Role::ROLE_ADD_PROJECT,
			'subject'     => $this->projectStub($author),
			'token'       => $this->tokenStub($loggedUser),
			'security'    => $security,
			'expectation' => true,
		];
		// project lead can delete own project
		$author = $this->createStub(User::class);
		$author->expects(static::once())
			   ->method('getId')
			   ->willReturn(1)
		;
		$loggedUser = $this->createStub(User::class);
		$loggedUser->expects(static::once())
				   ->method('getId')
				   ->willReturn(1)
		;
		$security = $this->createStub(Security::class);
		$security->method('isGranted')
				 ->withConsecutive([Role::ROLE_ADMIN], [Role::ROLE_DELETE_PROJECT])
				 ->willReturnOnConsecutiveCalls(false, true)
		;
		yield 'project lead can delete own project' => [
			'attribute'   => Role::ROLE_DELETE_PROJECT,
			'subject'     => $this->projectStub($author),
			'token'       => $this->tokenStub($loggedUser),
			'security'    => $security,
			'expectation' => true,
		];
		// project lead can not delete other peoples project
		$author = $this->createStub(User::class);
		$author->expects(static::exactly(2))
			   ->method('getId')
			   ->willReturn(1)
		;
		$loggedUser = $this->createStub(User::class);
		$loggedUser->expects(static::exactly(2))
				   ->method('getId')
				   ->willReturn(2)
		;
		$security = $this->createStub(Security::class);
		$security->method('isGranted')
				 ->withConsecutive([Role::ROLE_ADMIN], [Role::ROLE_DELETE_PROJECT])
				 ->willReturnOnConsecutiveCalls(false, true)
		;
		yield 'project lead can not delete other peoples project' => [
			'attribute'   => Role::ROLE_DELETE_PROJECT,
			'subject'     => $this->projectStub($author),
			'token'       => $this->tokenStub($loggedUser),
			'security'    => $security,
			'expectation' => false,
		];
		// project lead or a regular user can edit if they are in the editable group
		$author = $this->createStub(User::class);
		$author->expects(static::exactly(2))
			   ->method('getId')
			   ->willReturn(1)
		;
		$loggedUser = $this->createStub(User::class);
		$loggedUser->expects(static::exactly(2))
				   ->method('getId')
				   ->willReturn(2)
		;
		$security = $this->createStub(Security::class);
		$security->method('isGranted')
				 ->with(Role::ROLE_ADMIN)
				 ->willReturn(false)
		;
		$editors = $this->createStub(Collection::class);
		$editors->expects(static::once())
				->method('contains')
				->with($loggedUser)
				->willReturn(true)
		;
		$project = $this->createStub(Project::class);
		$project->expects(static::once())
				->method('getCanEdit')
				->willReturn($editors)
		;
		yield 'project lead or a regular user can edit if they are in the editable group' => [
			'attribute'   => Role::ROLE_EDIT_PROJECT,
			'subject'     => $project,
			'token'       => $this->tokenStub($loggedUser),
			'security'    => $security,
			'expectation' => true,
		];
		// project lead or a regular user can see if they are in the editable group
		$author = $this->createStub(User::class);
		$author->expects(static::exactly(2))
			   ->method('getId')
			   ->willReturn(1)
		;
		$loggedUser = $this->createStub(User::class);
		$loggedUser->expects(static::exactly(2))
				   ->method('getId')
				   ->willReturn(2)
		;
		$security = $this->createStub(Security::class);
		$security->method('isGranted')
				 ->with(Role::ROLE_ADMIN)
				 ->willReturn(false)
		;
		$editors = $this->createStub(Collection::class);
		$editors->expects(static::once())
				->method('contains')
				->with($loggedUser)
				->willReturn(true)
		;
		$project = $this->createStub(Project::class);
		$project->expects(static::once())
				->method('getCanEdit')
				->willReturn($editors)
		;
		yield 'project lead or a regular user can see if they are in the editable group' => [
			'attribute'   => Role::ROLE_VIEW_PROJECT,
			'subject'     => $project,
			'token'       => $this->tokenStub($loggedUser),
			'security'    => $security,
			'expectation' => true,
		];
		// project lead or a regular user can see if they are in the viewers group
		$author = $this->createStub(User::class);
		$author->expects(static::exactly(2))
			   ->method('getId')
			   ->willReturn(1)
		;
		$loggedUser = $this->createStub(User::class);
		$loggedUser->expects(static::exactly(2))
				   ->method('getId')
				   ->willReturn(2)
		;
		$security = $this->createStub(Security::class);
		$security->method('isGranted')
				 ->with(Role::ROLE_ADMIN)
				 ->willReturn(false)
		;
		$viewers = $this->createStub(Collection::class);
		$viewers->expects(static::once())
				->method('contains')
				->with($loggedUser)
				->willReturn(true)
		;
		$project = $this->createStub(Project::class);
		$project->expects(static::once())
				->method('getCanView')
				->willReturn($viewers)
		;
		yield 'project lead or a regular user can see if they are in the viewers group' => [
			'attribute'   => Role::ROLE_VIEW_PROJECT,
			'subject'     => $project,
			'token'       => $this->tokenStub($loggedUser),
			'security'    => $security,
			'expectation' => true,
		];
		// anon immediately gets denied
		$anonToken = $this->createStub(TokenInterface::class);
		$anonToken->expects(static::once())
				  ->method('getUser')
				  ->willReturn('IS_ANONYMOUS')
		;
		yield 'anon cannot add' => [
			'attribute'   => Role::ROLE_ADD_PROJECT,
			'subject'     => $this->createMock(Project::class),
			'token'       => $anonToken,
			'security'    => $this->createMock(Security::class),
			'expectation' => false,
		];
		$anonToken = $this->createStub(TokenInterface::class);
		$anonToken->expects(static::once())
				  ->method('getUser')
				  ->willReturn('IS_ANONYMOUS')
		;
		yield 'anon cannot edit' => [
			'attribute'   => Role::ROLE_EDIT_PROJECT,
			'subject'     => $this->createMock(Project::class),
			'token'       => $anonToken,
			'security'    => $this->createMock(Security::class),
			'expectation' => false,
		];
		$anonToken = $this->createStub(TokenInterface::class);
		$anonToken->expects(static::once())
				  ->method('getUser')
				  ->willReturn('IS_ANONYMOUS')
		;
		yield 'anon cannot view' => [
			'attribute'   => Role::ROLE_VIEW_PROJECT,
			'subject'     => $this->createMock(Project::class),
			'token'       => $anonToken,
			'security'    => $this->createMock(Security::class),
			'expectation' => false,
		];
		$anonToken = $this->createStub(TokenInterface::class);
		$anonToken->expects(static::once())
				  ->method('getUser')
				  ->willReturn('IS_ANONYMOUS')
		;
		yield 'anon cannot delete' => [
			'attribute'   => Role::ROLE_DELETE_PROJECT,
			'subject'     => $this->createMock(Project::class),
			'token'       => $anonToken,
			'security'    => $this->createMock(Security::class),
			'expectation' => false,
		];
	}
	
	private function adminSecurity(): Security {
		$security = $this->createStub(Security::class);
		$security->expects(static::once())
				 ->method('isGranted')
				 ->with(Role::ROLE_ADMIN)
				 ->willReturn(true)
		;
		return $security;
	}
	
	private function adminToken(): TokenInterface {
		$user  = $this->createMock(User::class);
		$token = $this->createStub(TokenInterface::class);
		$token->expects(static::once())
			  ->method('getUser')
			  ->willReturn($user)
		;
		return $token;
	}
	
	private function adminProject(): Project {
		$project = $this->createStub(Project::class);
		$project->expects(static::never())
				->method('getAuthor')
		;
		return $project;
	}
	
	private function tokenStub(User $user): TokenInterface {
		$token = $this->createStub(TokenInterface::class);
		$token->expects(static::once())
			  ->method('getUser')
			  ->willReturn($user)
		;
		return $token;
	}
	
	private function projectStub(User $user): Project {
		$project = $this->createStub(Project::class);
		$project->expects(static::atLeast(1))
				->method('getAuthor')
				->willReturn($user)
		;
		return $project;
	}
}