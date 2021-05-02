<?php

declare(strict_types = 1);

namespace App\Service\FileHandler\Exception;

use App\Service\FileHandler\Contracts\FileHandlerInterface;
use Exception;

class NoHandlerFoundForFileException extends Exception {
	public function __construct(string $extension) {
		parent::__construct(sprintf('No handler is configured for file format "%s".', $extension));
	}
}