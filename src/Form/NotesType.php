<?php

namespace App\Form;

use App\Entity\Eleves;
use App\Entity\Evaluations;
use App\Entity\Matieres;
use App\Entity\Notes;
use App\Repository\MatieresRepository;
use Doctrine\DBAL\Types\FloatType;
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
            ->add('note')
            ->add('evaluation', EntityType::class, [
                'class' => Evaluations::class,
                'choice_label' => 'nom',
            ])
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
            ->add('trimestre', ChoiceType::class, [
                'choices' => [
                    1 => 1,
                    2 => 2,
                    3 => 3,
                ]
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
