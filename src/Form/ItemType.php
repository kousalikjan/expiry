<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Item;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Constraints\Length;


class ItemType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => "Item name",
                'constraints' => [new Length(['max' => 20, 'min' => 3])]
            ])
            ->add('category', EntityType::class, [
                'required' => true,
                'label' => 'Category',
                'class' => Category::class,
                'choices' => $options['categories'],
                'choice_label' => 'name'
            ])
            ->add('amount', NumberType::class, [
                'required' => true,
                'label' => "Quantity",
                'empty_data' => 1,
            ])
            ->add('purchase', DateType::class, [
                'required' => false,
                'label' => 'Date of Purchase',
                'widget' => 'single_text'
            ])
            ->add('expiration', DateType::class, [
                'required' => false,
                'label' => 'Date of Expiration',
                'widget' => 'single_text'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
            'categories' => [],
        ]);
    }

}