<?php

declare(strict_types = 1);

namespace App\Form\Project;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChooseUserRemoveViewerType extends AbstractType {
	/**
	 * @param   FormBuilderInterface<FormBuilder>   $builder
	 * @param   array<mixed, mixed>                 $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options = []): void {
		$builder
			->add(
				'_canView',
				ChoiceType::class,
				[
					'choices'      => $builder->getData()->getCanView()->toArray(),
					'choice_label' => fn (User $user) => $user->getName(),
					'choice_value' => fn (?User $user) => $user?->getUuid(),
					'placeholder'  => 'Please choose a user to remove',
					'expanded'     => false,
					'multiple'     => true,
					'mapped'       => false,
				]
			)
			->add('save', SubmitType::class)
		;
	}
	
	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(
			[
				'data_class'      => Project::class,
				'csrf_field_name' => '_token',
				'csrf_token_id'   => '_choose_user[_csrf_token]',
			]
		);
	}
}