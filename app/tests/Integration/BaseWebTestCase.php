<?php

declare(strict_types = 1);

namespace App\Tests\Integration;

use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseWebTestCase extends WebTestCase {
	private static Generator $faker;
	private ?KernelBrowser   $client = null;
	
	public static function setUpBeforeClass(): void {
		static::$faker = Factory::create();
	}
	
	public function getFaker(): Generator {
		if (! isset(static::$faker)) {
			static::$faker = Factory::create();
		}
		return static::$faker;
	}
	
	public function getClient(): KernelBrowser {
		if ($this->client === null) {
			$this->client = static::createClient();
		}
		return $this->client;
	}
	
	protected function setUp(): void {
		ini_set('memory_limit', '512M');
		if ($this->client === null) {
			$this->client = static::createClient();
		}
		$this->client->restart();
	}
	
	protected function tearDown(): void {
//		echo 'pre reduce memory usage: ' . sprintf('%.2fM', memory_get_usage(true) / 1024 / 1024);
		
		// reduce memory usage
		
		// get all properties of self
		$refl = new \ReflectionObject($this);
		foreach ($refl->getProperties() as $prop) {
			// if not phpunit related or static
			if (! $prop->isStatic() && ! str_starts_with($prop->getDeclaringClass()->getName(), 'PHPUnit_')) {
				// make accessible and set value to free memory
				$prop->setAccessible(true);
				$prop->setValue($this, null);
			}
		}
//		echo 'post reduce memory usage: ' . sprintf('%.2fM', memory_get_usage(true) / 1024 / 1024);
		parent::tearDown();
	}
}