<?php

declare(strict_types = 1);

namespace App\Form\Bug;

use App\Entity\Bug;
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

class BugCreateType extends AbstractType {
	/**
	 * @param   FormBuilderInterface<FormBuilder>   $builder
	 * @param   array<mixed, mixed>                 $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder
			->add(
				'_title',
				TextType::class,
				[
					'required'    => true,
					'constraints' => [
						new NotBlank(),
						new NotNull(),
						new Length(max: 140, maxMessage: 'Maximum allowed length of {{ limit }} characters for bug title.'),
					],
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
						new Length(max: 10000, maxMessage: 'Maximum allowed length of {{ limit }} characters for bug description.'),
					],
				]
			)
			// todo keep this here for reference! works like a fucking charm!!!
			//			->add(
			//				'_project',
			//				EntityType::class,
			//				[
			//					'class'         => Project::class,
			//					'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('p')->orderBy('p.createdAt', 'DESC'),
			//					'choice_label'  => fn (Project $project) => $project->getName(),
			//					'choice_value'  => fn (?Project $project) => $project?->getUuid(),
			//					'placeholder'   => 'Please choose a project to create a bug for',
			//					'expanded'      => false,
			//					'multiple'      => false,
			//				]
			//			)
			->add('create', SubmitType::class)
		;
	}
	
	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(
			[
				'data_class'      => Bug::class,
				'csrf_field_name' => '_token',
				'csrf_token_id'   => '_bug_create[_csrf_token]',
			]
		);
	}
}