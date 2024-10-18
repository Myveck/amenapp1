<?php

namespace App\Form;

use App\Entity\AnneeScolaire;
use App\Entity\Ecoles;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ->add('annee_scolaire', EntityType::class, [
                'class' => AnneeScolaire::class,
                'choice_label' => 'annee',
                'label' => 'Année scolaire actuelle'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ecoles::class,
        ]);
    }
}
