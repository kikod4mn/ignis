<?php

declare(strict_types = 1);

namespace App\Service\FileHandler\Contracts;

use Symfony\Component\HttpFoundation\File\File;

interface FileHandlerInterface {
	public function handle(): bool;
	
	public function setFile(File $file): FileHandlerInterface;
	
	public function setConfig(array $config): FileHandlerInterface;
}