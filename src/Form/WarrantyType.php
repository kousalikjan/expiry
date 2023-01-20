<?php

namespace App\Form;

use App\Entity\Warranty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WarrantyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('expiration', DateType::class, [
                'required' => true,
                'label' => 'Date of Expiration',
                'widget' => 'single_text',
            ])
            ->add('notifyDaysBefore', NumberType::class, [
                'required' => false,
                'html5' => true,
                'label' => "Notify before expiration?",
                'attr' => ['placeholder' => "Empty for no notification", 'data-form-hide-target' => 'resetValue']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Warranty::class
        ]);
    }
}