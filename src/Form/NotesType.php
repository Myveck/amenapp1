<?php

namespace App\Form;

use App\Entity\Eleves;
use App\Entity\Matieres;
use App\Entity\Notes;
use App\Repository\MatieresRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $classe = $options['allow_extra_fields']->getId();
        // dd($classe);
        $builder
            ->add('note', ChoiceType::class, [
                'choices' => [
                    0 => 0,
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 4,
                    5 => 5,
                    6 => 6,
                    7 => 7,
                    8 => 8,
                    9 => 9,
                    10 => 10,
                    11 => 11,
                    12 => 12,
                    13 => 13,
                    14 => 14,
                    15 => 15,
                    16 => 16,
                    17 => 17,
                    18 => 18,
                    19 => 19,
                    20 => 20,
                ]
            ])
            ->add('type_evaluation')
            ->add('date_evaluation', null, [
                'widget' => 'single_text',
            ])
            ->add('eleve_id', EntityType::class, [
                'class' => Eleves::class,
                'choice_label' => 'nom',
            ])
            ->add('matiere_id', EntityType::class, [
                'class' => Matieres::class,
                'choice_label' => 'nom',
                'query_builder' => function (MatieresRepository $matieresRepository) use ($classe) {
                    return $matieresRepository->createQueryBuilder('m')
                        ->join('m.classesMatieres', 'cm')
                        ->join('cm.classe', 'c')
                        ->where('c.id = :classe')
                        ->setParameter('classe', $classe)
                        ->select('m');
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Notes::class,
        ]);
    }
}
