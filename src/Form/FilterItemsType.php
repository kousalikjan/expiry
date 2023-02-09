<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterItemsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'data' => $options['name']
            ])
            ->add('vendor', TextType::class, [
                'required' => false,
                'data' => $options['vendor']
            ])
            ->add('expires', NumberType::class, [
                'required' => false,
                'data' => $options['expires']
            ])
            ->add('sort', ChoiceType::class, [
                'required' => false,
                'choices' => ['Expiration' => 'expiration', 'Count' => 'amount'],
                'data' => $options['sort'],
                'placeholder' => 'Item name'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'sort' => 'name',
            'name' => null,
            'vendor' => null,
            'expires' => null
        ]);
    }
}