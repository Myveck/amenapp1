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
        $annee_actuelle = $this->anneeScolaireRepository->findOneBy(["actif" => true]);
        $classes = $this->classesRepository->findBy(
            ["annee_scolaire" => $annee_actuelle],
            ['classeOrder' => 'asc']
        );

        $classe = null;
        if ($trie === "all" || !$trie) {
            $inscriptions = $this->inscriptionRepository->findby([
				'AnneeScolaire' => $annee_actuelle,
                'actif' => true,
			], ['eleve' => 'ASC']);
            $trie = "all";
        } else {
            $classe = $this->classesRepository->find(intval($trie));
            $inscriptions = $this->inscriptionRepository->findby([
				'AnneeScolaire' => $annee_actuelle,
				'classe' => $classe,
                'actif' => true,
			], ['eleve' => 'ASC']);
        }

        return [
            'eleves' => $inscriptions,
            'classes' => $classes,
            'classe' => $classe,
            'active' => $trie,
            'nombre' => count($inscriptions),
            'annee_actuelle' => $annee_actuelle,
        ];
    }
}
