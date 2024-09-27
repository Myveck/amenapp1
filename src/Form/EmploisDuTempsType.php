<?php

namespace App\Form;

use App\Entity\Classes;
use App\Entity\EmploisDuTemps;
use App\Entity\Matieres;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmploisDuTempsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('jour', ChoiceType::class, [
                'choices' => [
                    'lundi' => 'Lundi',
                    'mardi' => 'Mardi',
                    'mercredi' => 'Mercredi',
                    'jeudi' => 'Jeudi',
                    'vendredi' => 'Vendredi',
                    'samedi' => 'Samedi',
                ],
            ])
            ->add('heure_debut', null, [
                'widget' => 'single_text',
            ])
            ->add('heure_fin', null, [
                'widget' => 'single_text',
            ])
            ->add('classe_id', EntityType::class, [
                'class' => Classes::class,
                'choice_label' => 'nom',
            ])
            ->add('matiere_id', EntityType::class, [
                'class' => Matieres::class,
                'choice_label' => 'nom',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EmploisDuTemps::class,
        ]);
    }
}
