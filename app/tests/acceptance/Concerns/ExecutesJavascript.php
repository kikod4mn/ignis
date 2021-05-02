<?php

declare(strict_types = 1);

namespace App\Tests\acceptance\Concerns;

use Facebook\WebDriver\JavaScriptExecutor;
use LogicException;
use Symfony\Component\Panther\Client;
use function sprintf;

trait ExecutesJavascript {
	protected function runJavascript(string $script): void {
		$webDriver = static::$client->getWebDriver();
		if (! $webDriver instanceof JavaScriptExecutor) {
			throw new LogicException(
				sprintf(
					'"%s" does not implement interface "%s" but method "executeScript()" is needed. Test cannot run.',
					$webDriver::class, JavaScriptExecutor::class
				)
			);
		}
		$webDriver->executeScript($script);
	}
}