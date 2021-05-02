<?php

declare(strict_types = 1);

namespace App\Service\FileHandler\Exception;

class FileNameNotAllowedException extends \Exception {
	public function __construct(string $filename) {
		parent::__construct(sprintf('File name "%s" is not allowed. Filename must contain only letters, numbers or underscore.', $filename));
	}
}