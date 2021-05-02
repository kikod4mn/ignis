<?php

declare(strict_types = 1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
	require dirname(__DIR__) . '/config/bootstrap.php';
} else {
	if (method_exists(Dotenv::class, 'bootEnv')) {
		(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
	}
}

if ((bool) $_SERVER['APP_DEBUG'] === false) {
	(new Symfony\Component\Filesystem\Filesystem())->remove(__DIR__ . '/../var/cache/test');
}
