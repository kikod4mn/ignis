<?php

declare(strict_types = 1);

namespace App\Tests\Extensions;

use App\Tests\TestDatabaseHelper;
use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;

class Boot implements BeforeFirstTestHook, AfterLastTestHook {
	public function executeBeforeFirstTest(): void {
		TestDatabaseHelper::reset();
	}
	
	public function executeAfterLastTest(): void { }
}