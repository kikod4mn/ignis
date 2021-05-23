<?php

declare(strict_types = 1);

namespace App\Tests;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

final class TestDatabaseHelper {
	private static string $actual = '';
	private static string $backUp = '';
	
	public static function reset(bool $reset = false): void {
		self::init();
		self::loadBackup($reset);
	}
	
	private static function init(): void {
		self::$actual = dirname(__DIR__) . '/var/db/test.db3';
		self::$backUp = dirname(__DIR__) . '/var/db/test-backup.db3';
	}
	
	private static function loadBackup(bool $reset): void {
		if ($reset || ! file_exists(self::$backUp) || filemtime(self::$actual) < time() - 600) {
			self::createBackup($reset);
		}
		copy(self::$backUp, self::$actual);
	}
	
	private static function createBackup(bool $reset): void {
		self::resetDb();
		copy(self::$actual, self::$backUp);
	}
	
	private static function resetDb(): void {
		$kernel = new Kernel('test', true);
		$kernel->boot();
		$app = new Application($kernel);
		$cmd = $app->find('app:doctrine:fresh');
		$app->setAutoExit(false);
		$input  = new ArrayInput(['command' => 'app:doctrine:fresh']);
		$output = new ConsoleOutput();
		$app->add($cmd);
		$cmd->run($input, $output);
	}
}