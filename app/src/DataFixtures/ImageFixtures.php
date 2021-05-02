<?php

declare(strict_types = 1);

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Project;
use App\Entity\User;
use App\Service\TimeCreator;
use DirectoryIterator;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Kikopolis\Str;
use Kikopolis\StringWrapper;
use Throwable;
use function dirname;
use function file_exists;
use function imagecolorallocate;
use function imagecolordeallocate;
use function imagecreate;
use function imagedestroy;
use function imagejpeg;
use function imagesetthickness;
use function imagestring;
use function is_dir;
use function ltrim;
use function mkdir;
use function mt_rand;
use function rmdir;
use function sprintf;
use function unlink;
use const DIRECTORY_SEPARATOR;

final class ImageFixtures extends BaseFixture implements DependentFixtureInterface {
	private string $imgDir = '';
	
	public function loadData(): void {
		$this->imageDirectory();
		$this->createMany(
			Image::class, 150, function (Image $image) {
			$image->setAuthor($this->getRandomRef(User::class));
			if (mt_rand(0, 1) === 1) {
				/** @var Project $project */
				$project = $this->getRandomRef(Project::class);
				$image->setProjectCover($project);
				$file = (string) (new StringWrapper($this->createImage($project->getName())))
					->after("public");
				$image->setPath(ltrim($file, '/\\'));
			} else {
				/** @var User $user */
				$user = $this->getRandomRef(User::class);
				$image->setUserAvatar($user);
				$file = (string) (new StringWrapper($this->createImage($user->getName())))
					->after("public");
				$image->setPath(ltrim($file, '/\\'));
			}
			$image->setCreatedAt(TimeCreator::randomPast());
			if (mt_rand(0, 1) === 140) {
				$image->setUpdatedAt(TimeCreator::randomPast());
			}
			$image->generateUuid();
		}
		);
	}
	
	/**
	 * @return array<int, string>
	 */
	public function getDependencies(): array {
		return [UserFixtures::class, ProjectFixtures::class];
	}
	
	private function imageDirectory(): void {
		if ($this->imgDir === '') {
			$this->imgDir = sprintf(
				'%s%spublic%simages%suploads',
				dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR
			);
		}
		$this->cleanDirectory($this->imgDir);
		if (! file_exists($this->imgDir) && ! is_dir($this->imgDir)) {
			mkdir(directory: $this->imgDir, recursive: true);
		}
	}
	
	private function createImage(string $text): string {
		try {
			$img = imagecreate(250, 80);
		} catch (Throwable $e) {
			throw new Exception($e->getMessage());
		}
		$file      = sprintf('%s%s%s.jpg', $this->imgDir, DIRECTORY_SEPARATOR, Str::random(45));
		$colors    = $this->getRandomColorPair();
		$bgColor   = imagecolorallocate($img, ...$colors['bg']);
		$textColor = imagecolorallocate($img, ...$colors['text']);
		imagestring($img, 10, 1, 25, $text, $textColor);
		imagesetthickness($img, 5);
		imagejpeg($img, $file, 65);
		imagecolordeallocate($img, $bgColor);
		imagecolordeallocate($img, $textColor);
		imagedestroy($img);
		return $file;
	}
	
	/**
	 * @return array<string, array<int, int>>
	 */
	#[ArrayShape(['bg' => "int[]", 'text' => "int[]"])]
	private function getRandomColorPair(): array {
		return ['bg' => [255, 255, 255], 'text' => [0, 0, 0]];
	}
	
	private function cleanDirectory(string $directory): void {
		if (file_exists($directory) && is_dir($directory)) {
			foreach (new DirectoryIterator($directory) as $file) {
				if ($file->isDot()) {
					continue;
				}
				if ($file->isFile() || $file->isLink()) {
					unlink($file->getPathname());
				}
				if ($file->isDir()) {
					$this->cleanDirectory($directory);
					return;
				}
			}
			try {
				rmdir($directory);
			} catch (Throwable) {
				try {
					rmdir($directory);
				} catch (Throwable $e) {
					throw new \Exception($e->getMessage());
				}
			}
		}
		//		foreach (scandir($this->imgDir) as $file) {
//			if ($file === '.' || $file === '..') {
//				continue;
//			}
//			if (! is_dir($file) || is_link($file)) {
//				unlink(sprintf("%s%s%s", $this->imgDir, DIRECTORY_SEPARATOR, $file));
//			}
//		}
	}
}