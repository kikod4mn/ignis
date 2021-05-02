<?php

declare(strict_types = 1);

namespace App\Form\Category;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class CategoryEditType extends AbstractType {
	/**
	 * @param   FormBuilderInterface<FormInterface>   $builder
	 * @param   array<mixed, mixed>                   $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder
			->add(
				'_name', TextType::class, [
						   'required'    => true,
						   'constraints' => [
							   new NotNull(),
							   new NotBlank(),
							   new Length(max: 255, maxMessage: 'Maximum allowed length for category name is {{ limit }} characters.'),
						   ],
					   ]
			)
			->add('save', SubmitType::class)
		;
	}
	
	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefaults(
			[
				'data_class'      => Category::class,
				'csrf_field_name' => '_token',
				'csrf_token_id'   => '_category_edit[_csrf_token]',
			]
		);
	}
}