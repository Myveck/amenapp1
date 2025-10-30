<?php

namespace App\Repository;

use App\Entity\Classes;
use App\Entity\Eleves;
use App\Entity\Inscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Expr\Cast\Array_;

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

    public function findByAnneActuelle(Eleves $eleve): Array
    {
        return $this->createQueryBuilder('i')
            ->join('i.AnneeScolaire', 'a')
            ->where('i.eleve = :eleve')
            ->andWhere('a.actif = true')
            ->andWhere('i.actif = true')
            ->setParameter('eleve', $eleve)
            ->getQuery()
            ->getResult();
    }

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
            ->getQuery()
            ->getResult();
    }

    public function findEleveActif(int $id): Eleves
    {
        return $this->createQueryBuilder('i')
        
            ->from(Eleves::class, 'e')
            ->join('i.eleve', 'ie')
            ->join('i.AnneeScolaire', 'a')
            ->where('a.actif = true')
            ->andWhere('i.actif = true')
            ->andWhere('ie = e')
            ->andWhere('ie.id = :id')
            ->setParameter('id', $id)
            ->select('DISTINCT e')
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
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
