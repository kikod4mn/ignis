<?php

declare(strict_types = 1);

namespace App\Form\Language;

use App\Entity\Language;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class LanguageEditType extends AbstractType {
	/**
	 * @param   FormBuilderInterface<FormBuilder>   $builder
	 * @param   array<mixed, mixed>                 $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder
			->add(
				'_name', TextType::class, [
						   'required'    => true,
						   'constraints' => [
							   new NotBlank(),
							   new NotNull(),
							   new Length(max: 50, maxMessage: 'Maximum length is {{ limit }} for language name.'),
						   ],
					   ]
			)
			->add(
				'_description', TextareaType::class, [
								  'required'    => true,
								  'constraints' => [
									  new NotBlank(),
									  new NotNull(),
									  new Length(max: 450, maxMessage: 'Maximum length for description is {{ limit }}.'),
								  ],
							  ]
			)
			->add('save', SubmitType::class)
		;
	}
	
	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(
			[
				'data_class'      => Language::class,
				'csrf_field_name' => '_token',
				'csrf_token_id'   => '_language_edit[_csrf_token]',
			]
		);
	}
}