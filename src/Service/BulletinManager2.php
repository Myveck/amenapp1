<?php

namespace App\Service;

use App\Entity\Classes;
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
        $cMatieres = $this->classesMatieresRepository->findBy(['classe' => $classe]);
        $allMatieres = [];
        
        foreach ($cMatieres as $key => $value) {
            $allMatieres[] = $value->getMatiere();
        }

        if (!$classe instanceof Classes) {
            throw new \InvalidArgumentException("Classe introuvable.");
        }

        $inscriptions = $this->inscriptionRepository->findBy([
            'classe' => $classe,
            'actif' => true,
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
        $eleves = [];

        /** @var Inscription $inscription */
        foreach ($inscriptions as $inscription) {
            $eleve = $inscription->getEleve();
            $eleves[] = $eleve;
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
                        'examinations' => $exam,
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

        // J'ajoute les rangs des élèves dans le tableau des résultats
        $results = $this->orderByRank($results);

        return [$results, $classe, $eleves, $totalCoef, $allMatieres];
    }

    public function orderByRank(array $results): array
    {
        $ranks = [];
        foreach ($results as $value) {
            $ranks[$value['eleve']->getId()] = $value['moyenneGenerale'];
        }

        arsort($ranks);
        $i = 1;
        foreach ($ranks as $key => $rank) {
            $results[$key]['rang'] = $i;
            $i++;
        }

        return $results;
    }

    public function calculateBilan(array $results): array
    {
        $bilanClasse = [];
        $moyGenClasse = [];
        $succes = 0;
        $fail = 0;

        foreach ($results as $result) {
            $moyGenClasse[] = $result['moyenneGenerale'];

            if ($result['moyenneGenerale'] < 10) {
                $fail +=1;
            } else {
                $succes +=1;
            }
        }

        $bilanClasse['moyenneClasse'] = round(array_sum($moyGenClasse) / count($moyGenClasse), 2);
        $bilanClasse['moyenneForte'] = max($moyGenClasse);
        $bilanClasse['moyenneFaible'] = min($moyGenClasse);
        $bilanClasse['admis'] = $succes;
        $bilanClasse['echoues'] = $fail;
        $bilanClasse['tauxAdmis'] = round(($succes * 100) / count($moyGenClasse));

        return $bilanClasse;
    }

    public function calculateAnnuelle(int $classeId): array
    {
        $classe = $this->classesRepository->find($classeId);
        $inscriptions = $this->inscriptionRepository->findBy([
            'classe' => $classe,
            'actif' => true,
        ]);

        // Récupération des moyennes trimestrielles
        $t1 = $this->calculateTrimestre($classeId, 1);
        $t2 = $this->calculateTrimestre($classeId, 2);
        $t3 = $this->calculateTrimestre($classeId, 3);

        $moyennesAnnuelles = [];

        foreach ($inscriptions as $inscription) {
            $eleve = $inscription->getEleve();
            $eleveId = $eleve->getId();

            $moy1 = $t1[$eleveId]['moyenneGenerale'] ?? null;
            $moy2 = $t2[$eleveId]['moyenneGenerale'] ?? null;
            $moy3 = $t3[$eleveId]['moyenneGenerale'] ?? null;

            // On garde uniquement les moyennes existantes
            $moyennesExistantes = array_filter([$moy1, $moy2, $moy3], fn($v) => $v !== null);

            // Moyenne annuelle = somme / nombre de trimestres valides
            $moyenneAnnuelle = !empty($moyennesExistantes)
                ? array_sum($moyennesExistantes) / count($moyennesExistantes)
                : null;

            // 🎓 Détermination du passage
            if ($moyenneAnnuelle === null) {
                $decision = 'Non évalué';
            } elseif ($moyenneAnnuelle >= 10) {
                $decision = 'Admis(e)';
            } else {
                $decision = 'Redouble';
            }

            $moyennesAnnuelles[$eleveId] = [
                'eleve' => $eleve,
                'moyennes' => [
                    'T1' => $moy1,
                    'T2' => $moy2,
                    'T3' => $moy3,
                ],
                'moyenneAnnuelle' => $moyenneAnnuelle,
                'decision' => $decision,
            ];
        }

        return $moyennesAnnuelles;
    }

    public function getDecisionPassage(?float $moyenne, float $seuil = 9.50): string
    {
        if ($moyenne === null) {
            return 'Non évalué';
        }

        if ($moyenne >= $seuil) {
            return 'Admis(e)';
        }

        // Tu peux affiner selon les politiques de l’école
        if ($moyenne >= ($seuil - 0.5)) {
            return 'Ajourné(e)';
        }

        return 'Redouble';
    }


}
