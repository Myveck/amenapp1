<?php

namespace App\Repository;

use App\Entity\Classes;
use App\Entity\Eleves;
use App\Entity\Inscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inscription>
 */
class InscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inscription::class);
    }

    //    /**
    //     * @return Inscription[] Returns an array of Inscription objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Inscription
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findElevesByAnneeActuelle(): array
    {
        return $this->createQueryBuilder('i')
        
            ->from(Eleves::class, 'e')
            ->join('i.eleve', 'ie')
            ->join('i.AnneeScolaire', 'a')
            ->where('a.actif = true')
            ->andWhere('i.actif = true')
            ->andWhere('ie = e')
            ->select('DISTINCT e')
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findElevesActuelsByClasse(Classes $classe): array
    {
        return $this->createQueryBuilder('i')
            ->from(Eleves::class, 'e')
            ->join('i.eleve', 'ie')
            ->join('i.AnneeScolaire', 'a')
            ->where('a.actif = true')
            ->andWhere('i.actif = true')
            ->andWhere('ie = e')
            ->andWhere('i.classe = :classe')
            ->setParameter('classe', $classe)
            ->select('DISTINCT e')
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    

}
