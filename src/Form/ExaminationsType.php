<?php

namespace App\Form;

use App\Entity\Classes;
use App\Entity\Evaluations;
use App\Entity\Examinations;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExaminationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_examination', null, [
                'widget' => 'single_text',
            ])
            ->add('classe', EntityType::class, [
                'class' => Classes::class,
                'choice_label' => 'nom',
            ])
            ->add('evaluation', EntityType::class, [
                'class' => Evaluations::class,
                'choice_label' => 'nom',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Examinations::class,
        ]);
    }
}
