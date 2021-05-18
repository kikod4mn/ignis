<?php

declare(strict_types = 1);

namespace App\Form\User\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class PasswordForgottenType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder
			->add(
				'_email', EmailType::class,
				[
					'mapped'      => false,
					'required' => true,
					'constraints' => [
						new NotBlank(message: 'E-mail cannot be blank. Please enter a valid e-mail address.'),
						new Email(message: 'Invalid e-mail address. Please check your email and try again.'),
					],
				]
			);
	}
	
	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(
			[
				'csrf_field_name' => '_token',
				'csrf_token_id'   => '_password_forgotten[_csrf_token]',
			]
		);
	}
}