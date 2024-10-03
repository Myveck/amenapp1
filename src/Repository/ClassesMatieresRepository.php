<?php

namespace App\Repository;

use App\Entity\ClassesMatieres;
use App\Entity\Matieres;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClassesMatieres>
 */
class ClassesMatieresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClassesMatieres::class);
    }

    //    /**
    //     * @return ClassesMatieres[] Returns an array of ClassesMatieres objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ClassesMatieres
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findMatiereByClasseLevel($level)
    {
        return $this->createQueryBuilder('cm')
            ->join('cm.matiere', 'm') // Jointure avec Matiere
            ->join('cm.classe', 'c') // Jointure avec Classe
            ->where('c.niveau = :niveau')
            ->orderBy('c.classeOrder', 'asc') // Filtrer par l'id de la matière
            ->setParameter('niveau', $level) // Valeur du paramètre
            ->select('m') // Sélectionner uniquement les matières
            ->getQuery()
            ->getResult();
    }


    public function findMatiereByClasse($classe)
    {
        return $this->createQueryBuilder('cm')
            ->join('cm.matiere', 'm')
            ->join('cm.classe', 'c')
            ->where('cm.classe = :classe')
            ->setParameter('classe', $classe)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findCoefficientByClasseMatiere($classe, $matiere)
    {
        return $this->createQueryBuilder('cm')
            ->select('cm.coefficient') // S'assurer que 'coefficient' est le bon champ
            ->where('cm.classe = :classe')
            ->andWhere('cm.matiere = :matiere')
            ->setParameters(new ArrayCollection([
                "classe" => $classe,
                "matiere" => $matiere,
            ]))
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findCoefficientByMatiere(Matieres $matiere)
    {
        return $this->createQueryBuilder('cm')
            ->join('cm.classe', 'c')
            ->join('cm.matiere', 'm')
            ->where('cm.matiere = :matiere')
            ->setParameter('matiere', $matiere)
            ->select("cm.coefficient")
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }
}
