<?php

declare(strict_types = 1);

namespace App\Form\Concerns;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

trait ProvidesPasswordValidation {
	protected function passwordValidations(): array {
		return [
			new NotBlank(message: 'Password field cannot be blank.'),
			new NotNull(),
			new Length(
				min: 12,
				max: 8096,
				minMessage: 'Password must be at least {{ limit }} characters long.',
				maxMessage: '{{ limit }} ought to be enough, friend...'
			),
			new Regex(pattern: '/[A-Z]+/', message: 'Password must contain at least 1 capital letter.'),
			new Regex(pattern: '/[0-9]+/', message: 'Password must contain at least 1 number.'),
			new Regex(pattern: '/[$&+,:;=?@#]+/', message: 'Password must contain at least one symbol $&+,:;=?@#'),
		];
	}
	
	protected function optionalPasswordValidations(): array {
		return [
			new Length(
				min: 12,
				max: 8096,
				minMessage: 'Password must be at least {{ limit }} characters long.',
				maxMessage: '{{ limit }} ought to be enough, friend...'
			),
			new Regex(pattern: '/[A-Z]+/', message: 'Password must contain at least 1 capital letter.'),
			new Regex(pattern: '/[0-9]+/', message: 'Password must contain at least 1 number.'),
			new Regex(pattern: '/[$&+,:;=?@#]+/', message: 'Password must contain at least one symbol $&+,:;=?@#'),
		];
	}
}