<?php

declare(strict_types = 1);

namespace App\Form\Feature;

use App\Entity\Feature;
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

class FeatureCreateType extends AbstractType {
	/**
	 * @param   FormBuilderInterface<FormBuilder>   $builder
	 * @param   array<mixed, mixed>                 $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder
			->add(
				'_title', TextType::class, [
							'required'    => true,
							'constraints' => [
								new NotBlank(),
								new NotNull(),
								new Length(max: 140, maxMessage: 'Maximum allowed length is {{ limit }} characters.'),
							],
						]
			)
			->add(
				'_description', TextareaType::class, [
								  'required'    => true,
								  'constraints' => [
									  new NotBlank(),
									  new NotNull(),
									  new Length(max: 2024, maxMessage: 'Maximum allowed length is {{ limit }} characters.'),
								  ],
							  ]
			)
			->add('create', SubmitType::class, ['label' => 'Add New Feature'])
		;
	}
	
	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(
			[
				'data_class'      => Feature::class,
				'csrf_field_name' => '_token',
				'csrf_token_id'   => '_feature_create[_csrf_token]',
			]
		);
	}
}