<?php

declare(strict_types = 1);

namespace App\Form\User\Account;

use App\Entity\User;
use App\Form\Concerns\ProvidesPasswordValidation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class RegisterType extends AbstractType {
	use ProvidesPasswordValidation;
	
	/**
	 * @param   FormBuilderInterface<FormBuilder>   $builder
	 * @param   array<mixed, mixed>                 $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder
			->add(
				'_name', TextType::class,
				[
					'required'    => true,
					'constraints' => [
						new NotBlank(message: 'Name is required.'),
						new NotNull(),
						new Length(
							min: 4, max: 255, minMessage: 'Minimum {{ limit }} characters for name.', maxMessage: 'Maximum {{ limit }} characters for name.'
						),
					],
				]
			)
			->add(
				'_email', EmailType::class,
				[
					'required'    => true,
					'constraints' => [
						new NotBlank(message: 'Email is required.'),
						new NotNull(),
						new Length(
							min: 3,
							max: 255,
							minMessage: 'Minimum {{ limit }} characters for email.',
							maxMessage: 'Maximum {{ limit }} characters for email.'
						),
						new Email(),
					],
				]
			)
			->add(
				'_plainPassword', PasswordType::class,
				[
					'required'    => true,
					'constraints' => [
						...$this->passwordValidations(),
					],
				]
			)
			->add(
				'_agreeToTerms', CheckboxType::class,
				[
					'mapped'      => false,
					'required'    => true,
					'constraints' => [
						new IsTrue(message: 'You must agree to the terms.'),
					],
				]
			)
			->add('_register', SubmitType::class)
		;
	}
	
	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(
			[
				'data_class'      => User::class,
				'csrf_field_name' => '_token',
				'csrf_token_id'   => '_register[_csrf_token]',
			]
		);
	}
}