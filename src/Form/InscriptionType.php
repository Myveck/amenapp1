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

class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('classe', EntityType::class, [
                'class' => Classes::class,
                'choice_label' => 'nom',
                'label' => 'Classe',
                'query_builder' => function (\App\Repository\ClassesRepository $repo) {
                    return $repo->createQueryBuilder('c')
                        ->join('c.annee_scolaire', 'a')
                        ->where('a.actif = true')
                        ->orderBy('c.classeOrder', 'ASC');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inscription::class,
        ]);
    }
}
