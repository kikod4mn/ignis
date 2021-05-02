<?php

declare(strict_types = 1);

namespace App\Tests\Contracts;

interface DBAccessContract {
	public const DB_USER = 'root';
	public const DB_PWD  = '';
	public const DB_NAME = 'ignis';
	public const DB_HOST = '127.0.0.1';
}