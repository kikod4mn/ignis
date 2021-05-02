<?php

declare(strict_types = 1);

namespace App\Doctrine;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;
use function gmp_init;
use function gmp_strval;
use function str_pad;
use function str_replace;
use function substr_replace;

final class UuidEncoder {
	final public function encode(UuidInterface $uuid): string {
		return gmp_strval(
			gmp_init(
				str_replace('-', '', $uuid->toString()),
				16
			),
			62
		);
	}
	
	final public function decode(string $encoded): ?UuidInterface {
		try {
			return Uuid::fromString(
				array_reduce(
					[20, 16, 12, 8],
					fn ($uuid, $offset) => substr_replace($uuid, '-', $offset, 0),
					str_pad(
						gmp_strval(
							gmp_init($encoded, 62),
							16
						),
						32,
						'0',
						STR_PAD_LEFT
					)
				)
			);
		} catch (Throwable) {
			return null;
		}
	}
}