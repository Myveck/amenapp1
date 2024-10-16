<?php

namespace App\Form;

use App\Entity\Eleves;
use App\Entity\Paiements;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaiementsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montant')
            ->add('type')
            ->add('eleve_id', EntityType::class, [
                'class' => Eleves::class,
                'choice_label' => function (Eleves $eleve) {
                    return $eleve->getNom() . ' ' . $eleve->getPrenom();
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Paiements::class,
        ]);
    }
}
