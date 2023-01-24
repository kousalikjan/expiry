<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', EntityType::class, [
                'required' => false,
                'label' => 'Category where items will be moved into',
                'mapped' => false,
                'class' => Category::class,
                'choices' => $options['allCategories'],
                'choice_label' => 'name',
                'placeholder' => 'Delete items in '.$options['current']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'allCategories' => [],
            'current' => null,
        ]);
    }
}