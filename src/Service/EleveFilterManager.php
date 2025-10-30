<?php

namespace App\Service;

use App\Repository\AnneeScolaireRepository;
use App\Repository\ClassesRepository;
use App\Repository\InscriptionRepository;

class EleveFilterManager
{
    public function __construct(
        private AnneeScolaireRepository $anneeScolaireRepository,
        private ClassesRepository $classesRepository,
        private InscriptionRepository $inscriptionRepository,
    ) {}

    public function filterEleves(?string $trie = null): array
    {
        $annee_actuelle = $this->anneeScolaireRepository->findOneBy(["actif" => 1]);
        $classes = $this->classesRepository->findBy(
            ["annee_scolaire" => $annee_actuelle],
            ['classeOrder' => 'asc']
        );

        $classe = null;
        if ($trie === "all" || !$trie) {
            $eleves = $this->inscriptionRepository->findElevesByAnneeActuelle();
            $trie = "all";
        } else {
            $classe = $this->classesRepository->find($trie);
            $eleves = $this->inscriptionRepository->findElevesActuelsByClasse($classe);
        }

        return [
            'eleves' => $eleves,
            'classes' => $classes,
            'classe' => $classe,
            'active' => $trie,
            'nombre' => count($eleves),
            'annee_actuelle' => $annee_actuelle,
        ];
    }
}
