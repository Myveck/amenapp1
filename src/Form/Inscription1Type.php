<?php

namespace App\Form;

use App\Entity\AnneeScolaire;
use App\Entity\Classes;
use App\Entity\Eleves;
use App\Entity\Inscription;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Inscription1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('redouble')
            ->add('actif')
            ->add('dateInscription', null, [
                'widget' => 'single_text',
            ])
            ->add('moyenne_annuelle')
            ->add('eleve', EntityType::class, [
                'class' => Eleves::class,
                'choice_label' => 'id',
            ])
            ->add('classe', EntityType::class, [
                'class' => Classes::class,
                'choice_label' => 'id',
            ])
            ->add('AnneeScolaire', EntityType::class, [
                'class' => AnneeScolaire::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inscription::class,
        ]);
    }
}
