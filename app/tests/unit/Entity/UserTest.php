<?php

declare(strict_types = 1);

namespace App\Tests\unit\Entity;

use App\Entity\Bug;
use App\Entity\Feature;
use App\Entity\Image;
use App\Entity\Project;
use App\Entity\Role;
use App\Entity\User;
use App\Service\TimeCreator;
use App\Tests\unit\Concerns\FakerConcern;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @covers \App\Entity\User
 */
class UserTest extends TestCase {
	use FakerConcern;
	
	public function testNewUser(): void {
		$user = new User();
		static::assertNull($user->getId());
		static::assertNull($user->getUuid());
		static::assertNull($user->getName());
		static::assertNull($user->getEmail());
		static::assertEmpty($user->getOldEmails());
		static::assertNull($user->getSalt());
		static::assertNull($user->getPlainPassword());
		static::assertNull($user->getPassword());
		static::assertEmpty($user->getOldPasswordHashes());
		static::assertNull($user->getPasswordResetToken());
		static::assertNull($user->getPasswordResetTokenRequestedFromIp());
		static::assertNull($user->getPasswordResetTokenRequestedAt());
		static::assertNull($user->getPasswordResetTokenRequestedFromBrowser());
		static::assertNull($user->getEmailConfirmedAt());
		static::assertNull($user->getAgreedToTermsAt());
		static::assertNull($user->getUpdatedAt());
		static::assertNull($user->getCreatedAt());
		static::assertNull($user->getUsername());
		static::assertFalse($user->getDisabled());
		static::assertFalse($user->getActive());
		static::assertNull($user->getLastLoginFromIp());
		static::assertNull($user->getLastLoginFromBrowser());
		static::assertNull($user->getLastLoginFromBrowser());
		static::assertNull($user->getAvatar());
		static::assertInstanceOf(ArrayCollection::class, $user->getImages());
		static::assertCount(0, $user->getImages());
		static::assertInstanceOf(ArrayCollection::class, $user->getViewableProjects());
		static::assertCount(0, $user->getViewableProjects());
		static::assertInstanceOf(ArrayCollection::class, $user->getEditableProjects());
		static::assertCount(0, $user->getEditableProjects());
		static::assertInstanceOf(ArrayCollection::class, $user->getProjects());
		static::assertCount(0, $user->getProjects());
		static::assertInstanceOf(ArrayCollection::class, $user->getBugs());
		static::assertCount(0, $user->getBugs());
		static::assertInstanceOf(ArrayCollection::class, $user->getImages());
		static::assertCount(0, $user->getImages());
		static::assertEquals([Role::ROLE_USER], $user->getRoles());
	}
	
	public function testUserSetters(): void {
		$user = new User();
		$user->generateUuid();
		static::assertInstanceOf(UuidInterface::class, $user->getUuid());
		$name = 'user of userland';
		$user->setName($name);
		static::assertEquals($name, $user->getName());
		$email = 'userland@example.com';
		$user->setEmail($email);
		static::assertEquals($email, $user->getEmail());
		$user->addOldEmail($email);
		static::assertCount(1, $user->getOldEmails());
		static::assertEquals($email, $user->getOldEmails()[0]);
		$image = $this->createMock(Image::class);
		$user->setAvatar($image);
		static::assertInstanceOf(Image::class, $user->getAvatar());
		$password = 'userland';
		$user->setPassword($password);
		static::assertEquals($password, $user->getPassword());
		$user->addOldPasswordHash($password);
		static::assertCount(1, $user->getOldPasswordHashes());
		static::assertEquals($password, $user->getOldPasswordHashes()[0]);
		$plainPassword = 'password';
		$user->setPlainPassword($plainPassword);
		static::assertEquals($plainPassword, $user->getPlainPassword());
		$pwdToken = 'password_reset_token';
		$user->setPasswordResetToken($pwdToken);
		static::assertEquals($pwdToken, $user->getPasswordResetToken());
		$pwdTokenRequestedAt = TimeCreator::randomPast();
		$user->setPasswordResetTokenRequestedAt($pwdTokenRequestedAt);
		static::assertEquals($pwdTokenRequestedAt, $user->getPasswordResetTokenRequestedAt());
		$pwdTokenRequestedIp = $this->getFaker()->unique()->ipv4;
		$user->setPasswordResetTokenRequestedFromIp($pwdTokenRequestedIp);
		static::assertEquals($pwdTokenRequestedIp, $user->getPasswordResetTokenRequestedFromIp());
		$pwdTokenBrowser = $this->getFaker()->unique()->userAgent;
		$user->setPasswordResetTokenRequestedFromBrowser($pwdTokenBrowser);
		static::assertEquals($pwdTokenBrowser, $user->getPasswordResetTokenRequestedFromBrowser());
		$emailConfirmToken = 'email_confirm';
		$user->setEmailConfirmToken($emailConfirmToken);
		static::assertEquals($emailConfirmToken, $user->getEmailConfirmToken());
		$emailConfirmedAt = TimeCreator::randomPast();
		$user->setEmailConfirmedAt($emailConfirmedAt);
		static::assertEquals($emailConfirmedAt, $user->getEmailConfirmedAt());
		$user->setActive(true);
		static::assertTrue($user->getActive());
		$user->setDisabled(true);
		static::assertTrue($user->getDisabled());
		$agreedToTermsAt = TimeCreator::randomPast();
		$user->setAgreedToTermsAt($agreedToTermsAt);
		static::assertEquals($agreedToTermsAt, $user->getAgreedToTermsAt());
		$lastLoginAt = TimeCreator::randomPast();
		$user->setLastLoginAt($lastLoginAt);
		static::assertEquals($lastLoginAt, $user->getLastLoginAt());
		$lastLoginFromBrowser = $this->getFaker()->unique()->userAgent;
		$user->setLastLoginFromBrowser($lastLoginFromBrowser);
		static::assertEquals($lastLoginFromBrowser, $user->getLastLoginFromBrowser());
		$lastLoginFromIp = $this->getFaker()->unique()->ipv4;
		$user->setLastLoginFromIp($lastLoginFromIp);
		static::assertEquals($lastLoginFromIp, $user->getLastLoginFromIp());
	}
	
	public function testUserProjects(): void {
		$user           = new User();
		$projectOne     = new Project();
		$projectOneName = $this->getFaker()->unique()->sentence();
		$projectOne->setName($projectOneName);
		$projectTwo     = new Project();
		$projectTwoName = $this->getFaker()->unique()->sentence();
		$projectTwo->setName($projectTwoName);
		$user->addProject($projectOne);
		$user->addProject($projectTwo);
		static::assertCount(2, $user->getProjects());
		static::assertInstanceOf(Project::class, $user->getProjects()[0]);
		static::assertInstanceOf(Project::class, $user->getProjects()[1]);
		static::assertEquals($projectOneName, $user->getProjects()[0]?->getName());
		static::assertEquals($projectTwoName, $user->getProjects()[1]?->getName());
	}
	
	public function testUserViewableProjects(): void {
		$user           = new User();
		$projectOne     = new Project();
		$projectOneName = $this->getFaker()->unique()->sentence();
		$projectOne->setName($projectOneName);
		$projectTwo     = new Project();
		$projectTwoName = $this->getFaker()->unique()->sentence();
		$projectTwo->setName($projectTwoName);
		$user->addViewableProject($projectOne);
		$user->addViewableProject($projectTwo);
		static::assertCount(2, $user->getViewableProjects());
		static::assertInstanceOf(Project::class, $user->getViewableProjects()[0]);
		static::assertInstanceOf(Project::class, $user->getViewableProjects()[1]);
		static::assertEquals($projectOneName, $user->getViewableProjects()[0]?->getName());
		static::assertEquals($projectTwoName, $user->getViewableProjects()[1]?->getName());
	}
	
	public function testUserEditableProjects(): void {
		$user           = new User();
		$projectOne     = new Project();
		$projectOneName = $this->getFaker()->unique()->sentence();
		$projectOne->setName($projectOneName);
		$projectTwo     = new Project();
		$projectTwoName = $this->getFaker()->unique()->sentence();
		$projectTwo->setName($projectTwoName);
		$user->addEditableProject($projectOne);
		$user->addEditableProject($projectTwo);
		static::assertCount(2, $user->getEditableProjects());
		static::assertInstanceOf(Project::class, $user->getEditableProjects()[0]);
		static::assertInstanceOf(Project::class, $user->getEditableProjects()[1]);
		static::assertEquals($projectOneName, $user->getEditableProjects()[0]?->getName());
		static::assertEquals($projectTwoName, $user->getEditableProjects()[1]?->getName());
	}
	
	public function testUserBugs(): void {
		$user       = new User();
		$bugOne     = new Bug();
		$bugOneName = $this->getFaker()->unique()->sentence();
		$bugOne->setTitle($bugOneName);
		$bugTwo     = new Bug();
		$bugTwoName = $this->getFaker()->unique()->sentence();
		$bugTwo->setTitle($bugTwoName);
		$user->addBug($bugOne);
		$user->addBug($bugTwo);
		static::assertCount(2, $user->getBugs());
		static::assertInstanceOf(Bug::class, $user->getBugs()[0]);
		static::assertInstanceOf(Bug::class, $user->getBugs()[1]);
		static::assertEquals($bugOneName, $user->getBugs()[0]?->getTitle());
		static::assertEquals($bugTwoName, $user->getBugs()[1]?->getTitle());
	}
	
	public function testUserFeatures(): void {
		$user           = new User();
		$featureOne     = new Feature();
		$featureOneName = $this->getFaker()->unique()->sentence();
		$featureOne->setTitle($featureOneName);
		$featureTwo     = new Feature();
		$featureTwoName = $this->getFaker()->unique()->sentence();
		$featureTwo->setTitle($featureTwoName);
		$user->addFeature($featureOne);
		$user->addFeature($featureTwo);
		static::assertCount(2, $user->getFeatures());
		static::assertInstanceOf(Feature::class, $user->getFeatures()[0]);
		static::assertInstanceOf(Feature::class, $user->getFeatures()[1]);
		static::assertEquals($featureOneName, $user->getFeatures()[0]?->getTitle());
		static::assertEquals($featureTwoName, $user->getFeatures()[1]?->getTitle());
	}
	
	public function testUserImages(): void {
		$user         = new User();
		$imageOne     = new Image();
		$imageOneName = $this->getFaker()->unique()->filePath();
		$imageOne->setPath($imageOneName);
		$imageTwo     = new Image();
		$imageTwoName = $this->getFaker()->unique()->filePath();
		$imageTwo->setPath($imageTwoName);
		$user->addImage($imageOne);
		$user->addImage($imageTwo);
		static::assertCount(2, $user->getImages());
		static::assertInstanceOf(Image::class, $user->getImages()[0]);
		static::assertInstanceOf(Image::class, $user->getImages()[1]);
		static::assertEquals($imageOneName, $user->getImages()[0]?->getPath());
		static::assertEquals($imageTwoName, $user->getImages()[1]?->getPath());
	}
}