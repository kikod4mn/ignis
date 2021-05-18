<?php

declare(strict_types = 1);

namespace App\Form\User\Profile;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class EditType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder
			->add(
				'_name', TextType::class, [
						   'required'    => true,
						   'constraints' => [
							   new NotBlank(message: 'Name is required.'),
							   new NotNull(),
							   new Length(
								   min: 4,
								   max: 255,
								   minMessage: 'Minimum {{ limit }} characters for name.',
								   maxMessage: 'Maximum {{ limit }} characters for name.'
							   ),
						   ],
					   ]
			)
			->add('_save', SubmitType::class)
		;
	}
	
	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(
			[
				'data_class'      => User::class,
				'csrf_field_name' => '_token',
				'csrf_token_id'   => '_profile_edit[_csrf_token]',
			]
		);
	}
}