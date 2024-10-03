<?php

namespace App\Repository;

use App\Entity\Eleves;
use App\Entity\Matieres;
use App\Entity\Notes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notes>
 */
class NotesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notes::class);
    }

    //    /**
    //     * @return Notes[] Returns an array of Notes objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Notes
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByEleveTrimestre(Eleves $eleve, int $trimestre): ?array
    {
        return $this->createQueryBuilder('n')
            ->where('n.eleve = :eleve')
            ->andWhere('n.Trimestre = :trimestre')
            ->setParameter('eleve', $eleve)
            ->setParameter('trimestre', $trimestre)
            ->getQuery()
            ->getResult();
    }

    public function findByEleveEvaluationMatiere(Eleves $eleve, int $evaluation, Matieres $matiere): ?Notes
    {
        return $this->createQueryBuilder('n')
            ->where('n.eleve = :eleve')
            ->andWhere('n.evaluation = :evaluation')
            ->andWhere('n.matiere = :matiere')
            ->setParameter('eleve', $eleve)
            ->setParameter('evaluation', $evaluation)
            ->setParameter('matiere', $matiere)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
