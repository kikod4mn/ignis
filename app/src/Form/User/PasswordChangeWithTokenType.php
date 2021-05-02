<?php

declare(strict_types = 1);

namespace App\Form\User;

use App\Form\Concerns\ProvidesPasswordValidation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordChangeWithTokenType extends AbstractType {
	use ProvidesPasswordValidation;
	
	/**
	 * @param   FormBuilderInterface<FormBuilder>   $builder
	 * @param   array<mixed, mixed>                 $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder
			//			->add(
			//				'_password', RepeatedType::class, [
			//					           'type'            => PasswordType::class,
			//					           'mapped'          => false,
			//					           'invalid_message' => 'Passwords must match.',
			//					           'required'        => true,
			//					           'first_name'      => '_first',
			//					           'second_name'     => '_second',
			//					           'first_options'   => ['label' => 'Password'],
			//					           'second_options'  => ['label' => 'Repeat Password'],
			//					           'constraints'     => [
			//						           ...$this->passwordValidations(),
			//					           ],
			//				           ]
			//			);
			->add(
				'_password', PasswordType::class,
				[
					'required'    => true,
					'constraints' => [
						...$this->passwordValidations(),
					],
				]
			);
	}
	
	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(
			[
				'csrf_field_name' => '_token',
				'csrf_token_id'   => '_password_change_with_token[_csrf_token]',
			]
		);
	}
}
