<?php

declare(strict_types = 1);

namespace App\Tests\Integration;

use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseWebTestCase extends WebTestCase {
	private static Generator $faker;
	protected KernelBrowser  $client;
	
	public static function setUpBeforeClass(): void {
		static::$faker = Factory::create();
	}
	
	public function getFaker(): Generator {
		if (! isset(static::$faker)) {
			static::$faker = Factory::create();
		}
		return static::$faker;
	}
	
	protected function setUp(): void {
		if (! isset($this->client)) {
			$this->client = static::createClient();
		}
		$this->client->restart();
	}
}