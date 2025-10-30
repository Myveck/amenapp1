<?php

namespace App\Repository;

use App\Entity\Classes;
use App\Entity\Eleves;
use App\Entity\Evaluations;
use App\Entity\Examinations;
use App\Entity\Matieres;
use App\Entity\Notes;
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

    public function findByAnneActuelle() :array 
    {
        return $this->createQueryBuilder('e')
            ->join('e.annee_scolaire', 'a')
            ->Where('a.actif = true')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEleveNoteByEvaluation(Eleves $eleve, Evaluations $evaluation)
    {

        return $this->createQueryBuilder('e')
            ->from(Notes::class, 'n')
            ->join('e.note', 'en')
            ->join('e.annee_scolaire', 'a')
            ->join('e.evaluation', 'ev')
            ->andWhere('n.eleve = :eleve')
            ->andWhere('ev = :evaluation')
            ->andWhere('a.actif = true')
            ->setParameter('eleve', $eleve)
            ->setParameter('evaluation', $evaluation)
            ->select('Distinct n')
            ->getQuery()
            ->getResult();
    }

    public function findNotesByClasseEvalutionMatiere(Evaluations $evaluation): Notes
    {
        return $this->createQueryBuilder('ev')
            ->from(Notes::class, 'n')
            ->join('Notes::class', 'n')
            ->getQuery()
            ->getResult();
    }

    public function findWithEvaluations($classe, $trimestre, $matiere, $anneeScolaire)
{
    return $this->createQueryBuilder('e')
        ->leftJoin('e.evaluation', 'ev')->addSelect('ev')
        ->where('e.classe = :classe')
        ->andWhere('e.trimestre = :trimestre')
        ->andWhere('e.matiere = :matiere')
        ->andWhere('e.annee_scolaire = :annee')
        ->setParameter('classe', $classe)
        ->setParameter('trimestre', $trimestre)
        ->setParameter('matiere', $matiere)
        ->setParameter('annee', $anneeScolaire)
        ->getQuery()
        ->getResult();
    }

    public function findUniqueByClasseAndMatiere(?Classes $classe = null, ?int $trimestre = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e')
            ->groupBy('e.classe, e.matiere, e.trimestre')
            ->orderBy('e.id', 'DESC');

        if ($classe) {
            $qb->andWhere('e.classe = :classe')
            ->setParameter('classe', $classe);
        }

        if ($trimestre) {
            $qb->andWhere('e.trimestre = :trimestre')
            ->setParameter('trimestre', $trimestre);
        }

        return $qb->getQuery()->getResult();
    }

}
