<?php

declare(strict_types = 1);

namespace App\Service\FileHandler\Exception;

use Exception;
use Throwable;

class InitFunctionNotRunException extends Exception {
	public function __construct() {
		parent::__construct('The function "init()" has not been called on the FileUploader instance.');
	}
}