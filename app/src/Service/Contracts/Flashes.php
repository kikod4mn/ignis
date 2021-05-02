<?php

declare(strict_types = 1);

namespace App\Service\Contracts;

interface Flashes {
	const INFO_MESSAGE    = 'INFO_MESSAGE';
	const SUCCESS_MESSAGE = 'SUCCESS_MESSAGE';
	const WARNING_MESSAGE = 'WARNING_MESSAGE';
	const DANGER_MESSAGE  = 'DANGER_MESSAGE';
}