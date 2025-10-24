<?php

namespace App\Form;

use App\Entity\Classes;
use App\Entity\Eleves;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Eleves1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('date_naissance', null, [
                'widget' => 'single_text',
            ])
            ->add("sexe", ChoiceType::class, [
                'choices' => [
                    'masculin' => 'm',
                    'feminin' => 'f',
                ]
            ])
            ->add('inscriptions', CollectionType::class, [
                'entry_type' => InscriptionType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => false,
                'by_reference' => false,
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Eleves::class,
        ]);
    }
}
