<?php

declare(strict_types = 1);

namespace App\Form\Project;

use App\Entity\Category;
use App\Entity\Project;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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

class ProjectEditType extends AbstractType {
	/**
	 * @param   FormBuilderInterface<FormBuilder>   $builder
	 * @param   array<mixed, mixed>                 $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder
			->add(
				'_name',
				TextType::class,
				[
					'required'    => true,
					'constraints' => [
						new NotBlank(),
						new NotNull(),
						new Length(max: 140, maxMessage: 'Maximum allowed length of {{ limit }} characters for project name.'),
					],
				]
			)
			->add(
				'_category', EntityType::class,
				[
					'class'        => Category::class,
					'choice_label' => 'name',
					'placeholder'  => 'Select app type',
					'required'     => true,
				]
			)
			->add(
				'_description',
				TextareaType::class,
				[
					'required'    => true,
					'constraints' => [
						new NotBlank(),
						new NotNull(),
						new Length(max: 10000, maxMessage: 'Maximum allowed length of {{ limit }} characters for project description.'),
					],
				]
			)
			->add('create', SubmitType::class)
		;
	}
	
	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(
			[
				'data_class'      => Project::class,
				'csrf_field_name' => '_token',
				'csrf_token_id'   => '_project_edit[_csrf_token]',
			]
		);
	}
}