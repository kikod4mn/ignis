<?php

declare(strict_types = 1);

namespace App\Service\FileHandler\Exception;

use Exception;

class FileExtensionUnknownException extends Exception {
	public function __construct(string $extension) {
		parent::__construct(sprintf('File extension "%s" is unknown.', $extension));
	}
}