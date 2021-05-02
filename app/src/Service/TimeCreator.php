<?php

declare(strict_types = 1);

namespace App\Service;

use Carbon\Carbon;
use DateTimeInterface;
use DateTimeZone;
use function mt_rand;

final class TimeCreator {
	public static function now(): DateTimeInterface {
		return Carbon::now(new DateTimeZone('Europe/Tallinn'));
	}
	
	public static function randomPast(): DateTimeInterface {
		return Carbon::now()->subDays(mt_rand(0, 620))->addHours(mt_rand(0, 23));
	}
	
	public static function randomFuture(): DateTimeInterface {
		return Carbon::now()->addDays(mt_rand(0, 620))->addHours(mt_rand(0, 23));
	}
}