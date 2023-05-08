<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Item;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ItemType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => "Item name"
            ])
            ->add('category', EntityType::class, [
                'required' => true,
                'label' => 'Category',
                'class' => Category::class,
                'choices' => $options['categories'],
                'choice_label' => 'name',
                'placeholder' => 'Select a category'
            ])
            ->add('amount', NumberType::class, [
                'required' => true,
                'html5' => true,
                'label' => "Quantity",
                'empty_data' => 1,
                'attr' => [
                    'step' => 0.01
                ]
            ])
            ->add('purchase', DateType::class, [
                'required' => false,
                'label' => 'Date of Purchase',
                'widget' => 'single_text'
            ])
            ->add('warranty', WarrantyType::class, [
                'required' => false
            ])
            ->add('vendor', TextType::class, [
                'required' => false
            ])
            ->add('price', NumberType::class, [
                'required' => false,
                'html5' => true,
                'scale' => 2,
                'attr' => [
                    'step' => 0.01
                ]
            ])
            ->add('currency', ChoiceType::class, [
                'required' => false,
                'choices' => ['CZK' => 'CZK', 'EUR' => 'EUR', 'USD' => 'USD'],
                'placeholder' => false,
            ])
            ->add('barcode', TextType::class, [
                'required' => false
            ])
            ->add('note', TextType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
            'categories' => [],
        ]);
    }

}