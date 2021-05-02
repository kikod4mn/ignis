<?php

declare(strict_types = 1);

namespace App\Service\FileHandler\Contracts;

use Symfony\Component\HttpFoundation\File\File;

interface FileUploaderInterface {
	public function init(File $file, array $config): void;
	
	public function upload(): bool;
}