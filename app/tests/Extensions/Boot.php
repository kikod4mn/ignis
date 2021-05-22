<?php

declare(strict_types = 1);

namespace App\Tests\Extensions;

use App\Kernel;
use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class Boot implements BeforeFirstTestHook, AfterLastTestHook {
	public function executeBeforeFirstTest(): void {
//		$kernel = new Kernel('test', true);
//		$kernel->boot();
//		$app = new Application($kernel);
//		$cmd = $app->find('app:doctrine:fresh');
//		$app->setAutoExit(false);
//		$input  = new ArrayInput(['command' => 'app:doctrine:fresh']);
//		$output = new ConsoleOutput();
//		$app->add($cmd);
//		$cmd->run($input, $output);
	}
	
	public function executeAfterLastTest(): void {
//		$kernel = new Kernel('test', true);
//		$kernel->boot();
//		$app = new Application($kernel);
//		$cmd = $app->find('app:doctrine:fresh');
//		$app->setAutoExit(false);
//		$input  = new ArrayInput(['command' => 'app:doctrine:fresh']);
//		$output = new ConsoleOutput();
//		$app->add($cmd);
//		$cmd->run($input, $output);
	}
}