<?php

declare(strict_types = 1);

namespace App\Service\FileHandler\Exception;

use Exception;

class TargetDirectoryCannotBeAccessedException extends Exception {
	public function __construct(string $directory) {
		parent::__construct(sprintf('Target directory "%s" cannot be accessed. Verify the directory exists and is writable.', $directory));
	}
}