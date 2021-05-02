<?php

declare(strict_types = 1);

namespace App\Service\FileHandler\Exception;

class FileNameTooLongException extends \Exception {
	public function __construct(string $filename, int $length) {
		parent::__construct(sprintf('Filename "%s" is too long. 64 characters or less, "%d" actual.', $filename, $length));
	}
}