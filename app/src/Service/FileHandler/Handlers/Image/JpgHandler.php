<?php

declare(strict_types = 1);

namespace App\Service\FileHandler\Handlers\Image;

use App\Service\FileHandler\Concerns\FileTypeHandlerTrait;
use App\Service\FileHandler\Contracts\FileHandlerInterface;

final class JpgHandler implements FileHandlerInterface {
	use FileTypeHandlerTrait;
}