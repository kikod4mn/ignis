<?php

declare(strict_types = 1);

namespace App\Service\FileHandler\Exception;

use Exception;
use Throwable;

final class InvalidUploadDirectoryException extends Exception {
	public function __construct(string $directory) {
		parent::__construct(sprintf('"%s" is not a valid directory.', $directory));
	}
}