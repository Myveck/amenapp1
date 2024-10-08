<?php

namespace App\Form;

use App\Entity\Ecoles;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcolesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('school_name')
            ->add('adresse')
            ->add('boite_postale')
            ->add('telephone')
            ->add('cellulaire')
            ->add('email')
            ->add('logo')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ecoles::class,
        ]);
    }
}
