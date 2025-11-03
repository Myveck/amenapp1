<?php

namespace App\Service;

use App\Entity\Classes;
use App\Entity\Eleves;
use App\Entity\Examinations;
use App\Entity\Inscription;
use App\Repository\ClassesMatieresRepository;
use App\Repository\ClassesRepository;
use App\Repository\EvaluationsRepository;
use App\Repository\ExaminationsRepository;
use App\Repository\InscriptionRepository;
use App\Repository\NotesRepository;

class BulletinManager2
{
    public function __construct(
        private ClassesMatieresRepository $classesMatieresRepository,
        private ExaminationsRepository $examinationsRepository,
        private NotesRepository $notesRepository,
        private EvaluationsRepository $evaluationsRepository,
        private ClassesRepository $classesRepository,
        private InscriptionRepository $inscriptionRepository,
    ) {}

    /**
     * Calcule toutes les notes du trimestre pour une classe donnée
     *
     * @param int $classeId
     * @param int $trimestre
     * @return array
     *
     * Structure retournée :
     * [
     *   eleveId => [
     *      'eleve' => Eleves,
     *      'matieres' => [
     *          matiereId => [
     *              'matiere' => Matieres,
     *              'notes' => [
     *                  'D1' => 12.5,
     *                  'D2' => 14.0,
     *                  'MI' => 13.0,
     *                  'DH' => 15.5,
     *              ],
     *              'moyenne' => 13.75,
     *              'coefficient' => 2
     *          ],
     *      ],
     *      'moyenneGenerale' => 14.1
     *   ],
     * ]
     */
    public function calculateTrimestre(int $classeId, int $trimestre): array
    {
        $classe = $this->classesRepository->find($classeId);
        if (!$classe instanceof Classes) {
            throw new \InvalidArgumentException("Classe introuvable.");
        }

        $inscriptions = $this->inscriptionRepository->findBy([
            'classe' => $classe,
            'active' => true,
        ]);

        $examinations = $this->examinationsRepository->findBy([
            'classe' => $classe,
            'trimestre' => $trimestre,
        ]);

        // Récupère les types d'évaluations (D1, D2, MI, DH)
        $evaluations = $this->evaluationsRepository->findAll();
        $evaluationMap = [];
        foreach ($evaluations as $evaluation) {
            $evaluationMap[$evaluation->getId()] = strtoupper(substr($evaluation->getNom(), 0, 2)); // Exemple : "Devoir 1" → "D1"
        }

        $results = [];

        /** @var Inscription $inscription */
        foreach ($inscriptions as $inscription) {
            $eleve = $inscription->getEleve();
            $eleveId = $eleve->getId();
            $results[$eleveId] = [
                'eleve' => $eleve,
                'matieres' => [],
                'moyenneGenerale' => 0,
            ];

            $totalNotes = 0;
            $totalCoef = 0;

            /** @var Examinations $exam */
            foreach ($examinations as $exam) {
                $matiere = $exam->getMatiere();
                $matiereId = $matiere->getId();
                $coef = $this->classesMatieresRepository->findOneBy([
                    'classe' => $classe,
                    'matiere' => $matiere,
                ])?->getCoefficient() ?? 1;

                $notesParEval = [];
                $sum = 0;
                $count = 0;

                foreach ($evaluationMap as $evalId => $evalCode) {
                    $noteEntity = $this->notesRepository->findOneBy([
                        'evaluation' => $evalId,
                        'examination' => $exam,
                        'eleve' => $eleve,
                    ]);

                    if ($noteEntity) {
                        $valeur = $noteEntity->getNote();
                        $notesParEval[$evalCode] = $valeur;
                        $sum += $valeur;
                        $count++;
                    } else {
                        $notesParEval[$evalCode] = null;
                    }
                }

                $moyenne = $count > 0 ? round($sum / $count, 2) : null;

                $results[$eleveId]['matieres'][$matiereId] = [
                    'matiere' => $matiere,
                    'notes' => $notesParEval,
                    'moyenne' => $moyenne,
                    'coefficient' => $coef,
                ];

                if ($moyenne !== null) {
                    $totalNotes += $moyenne * $coef;
                    $totalCoef += $coef;
                }
            }

            $results[$eleveId]['moyenneGenerale'] = $totalCoef > 0
                ? round($totalNotes / $totalCoef, 2)
                : null;
        }

        return $results;
    }
}
