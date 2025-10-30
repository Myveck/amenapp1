<?php

namespace App\Service;

use App\Repository\AnneeScolaireRepository;
use App\Repository\ClassesRepository;
use App\Repository\TarifRepository;

class ClasseManager
{
    public function __construct(
        private AnneeScolaireRepository $anneeScolaireRepository,
        private ClassesRepository $classesRepository,
        private TarifRepository $tarifRepository
    ) {}

    /**
     * Retourne les classes filtrées par niveau (ou toutes)
     * + les tarifs associés.
     */
    public function getClassesAndTarifs(?string $trie = null): array
    {
        // 1. Trouver l'année scolaire active
        $anneeScolaire = $this->anneeScolaireRepository->findOneBy(['actif' => 1]);
        if (!$anneeScolaire) {
            return ['classes' => [], 'classeTarif' => [], 'active' => 'none'];
        }

        // 2. Déterminer le filtre
        if (!$trie || $trie === "all") {
            $trie = "all";
            $classes = $this->classesRepository->findBy(
                ['annee_scolaire' => $anneeScolaire],
                ['classeOrder' => 'asc']
            );
        } else {
            $classes = $this->classesRepository->findBy(
                [
                    'niveau' => $trie,
                    'annee_scolaire' => $anneeScolaire,
                ],
                ['nom' => 'asc']
            );
        }

        // 3. Associer chaque classe à son tarif
        $classeTarif = [];
        foreach ($classes as $classe) {
            $classeTarif[$classe->getNom()] = $this->tarifRepository->findOneBy(['classe' => $classe]);
        }

        return [
            'classes' => $classes,
            'classeTarif' => $classeTarif,
            'active' => $trie,
        ];
    }
}
