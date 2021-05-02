<?php

declare(strict_types = 1);

namespace App\Tests;

use App\Tests\acceptance\Concerns\ExecutesJavascript;
use App\Tests\acceptance\Concerns\LogsUsersIn;
use App\Tests\Contracts\DBAccessContract;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

abstract class BasePantherTestClass extends PantherTestCase implements DBAccessContract {
	use LogsUsersIn;
	use ExecutesJavascript;
	
	protected static Client    $client;
	protected static Generator $faker;
	
	public static function setUpBeforeClass(): void {
		static::$client = static::createPantherClient();
		static::$faker  = Factory::create();
	}
	
	public function faker(): Generator {
		if (static::$faker === null) {
			static::$faker = Factory::create();
		}
		return static::$faker;
	}
	
	protected function getContainer(): ContainerInterface {
		if (null === self::$container) {
			static::bootKernel([]);
		}
		return self::$container;
	}
	
	protected function setUp(): void {
		static::$client->restart();
	}
}