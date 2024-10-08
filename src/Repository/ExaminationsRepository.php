<?php

namespace App\Repository;

use App\Entity\Eleves;
use App\Entity\Evaluations;
use App\Entity\Examinations;
use App\Entity\Matieres;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Examinations>
 */
class ExaminationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Examinations::class);
    }

    //    /**
    //     * @return Examinations[] Returns an array of Examinations objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Examinations
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findNoteByEleve(Eleves $eleve)
    {
        return $this->createQueryBuilder('e')
            ->join('e.matiere', 'm')
            ->join('e.eleve', 'el')
            ->where('el.eleve = :eleve')
            ->setParameter('eleve', $eleve)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findNoteByMatEvTrEl(Matieres $matiere, Evaluations $evaluation, int $trimestre, Eleves $eleve)
    {
        return $this->createQueryBuilder('e')
            ->join('e.matiere', 'm')
            ->join('e.classe', 'c')
            ->join('e.note', 'n')
            ->where('n.eleve = :eleve')
            ->andWhere('e.matiere = :matiere')
            ->andWhere('e.evaluation = :evalution')
            ->andWhere('e.trimestre = :trimestre')
            ->setParameter('eleve', $eleve)
            ->setParameter('matiere', $matiere)
            ->setParameter('evaluation', $evaluation)
            ->setParameter('trimestre', $trimestre)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
