<?php

declare(strict_types = 1);

namespace App\Form\User;

use App\Entity\User;
use App\Form\Concerns\ProvidesPasswordValidation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

class AccountEditType extends AbstractType {
	use ProvidesPasswordValidation;
	
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder
			->add(
				'_email', EmailType::class,
				[
					'required'    => false,
					'constraints' => [
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
					'required'    => false,
					'constraints' => [
						...$this->optionalPasswordValidations(),
					],
				]
			)
			->add('_save', SubmitType::class)
		;
	}
	
	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(['data_class' => User::class]);
	}
}