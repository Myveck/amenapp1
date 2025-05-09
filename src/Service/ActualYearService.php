<?php

namespace App\Service;
use App\Repository\AnneeScolaireRepository;

class ActualYearService
{
    private $annee_actuelle;

    public function __construct(AnneeScolaireRepository $repo)
    {
        
    }
}