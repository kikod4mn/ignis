<?php

declare(strict_types = 1);

namespace App\Service;

use Exception;
use function mb_strlen;
use function rand;
use function random_int;

/**
 * Class TokenGenerator
 * @package App\Service
 * @author  Kristo Leas <kristo.leas@gmail.com>
 */
final class TokenGenerator {
	private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	private const NUMBER   = '0123456789';
	private string $alphabets;
	private string $token;
	private int    $length;
	
	public function alphanumericToken(int $length): string {
		$this->length    = $length;
		$this->alphabets = self::ALPHABET . self::NUMBER;
		return $this->compose();
	}
	
	public function letterToken(int $length): string {
		$this->length    = $length;
		$this->alphabets = self::ALPHABET;
		return $this->compose();
	}
	
	public function numberedToken(int $length): string {
		$this->length    = $length;
		$this->alphabets = self::NUMBER;
		return $this->compose();
	}
	
	private function compose(): string {
		$this->token = '';
		for ($i = 0; $i < $this->length; $i++) {
			$this->token .= $this->char();
		}
		return $this->token;
	}
	
	private function char(): string {
		try {
			return $this->alphabets[random_int(0, $maxNumber = mb_strlen($this->alphabets) - 1)];
		} catch (Exception) {
			return $this->alphabets[rand(0, $maxNumber = mb_strlen($this->alphabets) - 1)];
		}
	}
}