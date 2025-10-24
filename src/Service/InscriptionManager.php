<?php

namespace App\Service;

use App\Entity\Eleves;
use App\Entity\Inscription;
use App\Repository\InscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;

class InscriptionManager
{

    public function reinscrire(Eleves $eleve, Inscription $nouvelleInscription, EntityManagerInterface $em, InscriptionRepository $repo)
    {
        $inscriptionsActuelles = $repo->findByAnneActuelle($eleve);

        foreach ($inscriptionsActuelles as $actuelle) {
            $actuelle->setActif(false);
        }

        $nouvelleInscription->setActif(true);
        $em->persist($nouvelleInscription);
        $em->flush();
    }
}