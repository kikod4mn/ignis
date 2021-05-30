<?php

declare(strict_types = 1);

namespace App\Form\User\Security;

use App\Form\Concerns\ProvidesPasswordValidation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType {
	use ProvidesPasswordValidation;
	
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder
			->add(
				'_plainPassword', PasswordType::class,
				[
					'required'    => true,
					'constraints' => [
						...$this->passwordValidations(),
					],
				]
			)
			->add('_reset', SubmitType::class)
		;
	}
	
	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults([]);
	}
}
