<?php

namespace App\Form;

use App\Entity\Classes;
use App\Entity\ClassesMatieres;
use App\Entity\EmploisDuTemps;
use App\Entity\Matieres;
use App\Repository\ClassesMatieresRepository;
use App\Repository\ClassesRepository;
use App\Repository\MatieresRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MatieresType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('classe', EntityType::class, [
                'class' => Classes::class,
                'choice_label' => 'nom',   // Or whatever field is the class name
                'query_builder' => function (ClassesRepository $repo) {
                    return $repo->createQueryBuilder('c')
                        ->join('c.classesMatieres', 'cm')
                        ->join('cm.annee_scolaire', 'a')
                        ->where('a.actif = true')
                        ->groupBy('c.id')   // avoid duplicates if 1 class has many matieres
                        ->orderBy('c.nom', 'ASC');
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Matieres::class,
        ]);
    }
}
