<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DefaultSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('defaultCurrency', ChoiceType::class, [
                'required' => false,
                'choices' => ['CZK' => 'CZK', 'EUR' => 'EUR', 'USD' => 'USD'],
                'placeholder' => false,
            ])
            ->add('preferredLocale', ChoiceType::class, [
                'required' => false,
                'choices' => ['Čeština' => 'cs', 'English' => 'en'],
                'placeholder' => false,
                'label' => 'Preferred language'
            ])
            ->add('allowNotifications', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}