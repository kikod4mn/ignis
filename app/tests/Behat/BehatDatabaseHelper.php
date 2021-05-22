<?php

declare(strict_types = 1);

namespace App\Tests\Behat;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

final class BehatDatabaseHelper {
	public static function reset(bool $reset = false): void {
		self::loadBackup($reset);
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
	
	private static function createBackup(): void {
		self::resetDb();
		copy(dirname(__DIR__, 2) . '/var/db/test.db3', dirname(__DIR__, 2) . '/var/db/test-backup.db3');
	}
	
	private static function loadBackup(bool $reset): void {
		if ($reset || ! file_exists(dirname(__DIR__, 2) . '/var/db/test-backup.db3')) {
			self::createBackup();
		}
		if (file_exists(dirname(__DIR__, 2) . '/var/db/test-backup.db3')) {
			copy(dirname(__DIR__, 2) . '/var/db/test-backup.db3', dirname(__DIR__, 2) . '/var/db/test.db3');
		}
	}
}