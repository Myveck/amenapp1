<?php

namespace App\Service;

use App\Entity\Classes;
use App\Entity\Eleves;
use App\Repository\ClassesMatieresRepository;
use App\Repository\EvaluationsRepository;
use App\Repository\ExaminationsRepository;
use App\Repository\NotesRepository;

class BulletinManager
{
    private ClassesMatieresRepository $classesMatieresRepository;
    private ExaminationsRepository $examinationsRepository;
    private NotesRepository $notesRepository;
    private EvaluationsRepository $evaluationsRepository;

    public function __construct(
        ClassesMatieresRepository $classesMatieresRepository,
        ExaminationsRepository $examinationsRepository,
        NotesRepository $notesRepository,
        EvaluationsRepository $evaluationsRepository
    ) {
        $this->classesMatieresRepository = $classesMatieresRepository;
        $this->examinationsRepository = $examinationsRepository;
        $this->notesRepository = $notesRepository;
        $this->evaluationsRepository = $evaluationsRepository;
    }

    /**
     * 🔹 Calcule les moyennes et notes d’un trimestre pour chaque élève d’une classe
     */
    public function calculateTrimestre(Classes $classe, array $eleves, int $trimestre): array
    {
        // Récupère les 4 types d’évaluations (D1, D2, MI, DH)
        $evaluations = $this->evaluationsRepository->findBy(["id" => [1, 2, 3, 4]]);
        $evaluationsMap = [
            'd1' => $evaluations[0] ?? null,
            'd2' => $evaluations[1] ?? null,
            'mi' => $evaluations[2] ?? null,
            'dh' => $evaluations[3] ?? null,
        ];

        $matieres = $this->classesMatieresRepository->findMatiereByClasse($classe);

        $results = [];
        $moyennesParMatiere = [];

        foreach ($eleves as $eleve) {
            $id = $eleve->getId();
            $moyenneParMatiere = [];
            $moyenneCoef = [];
            $moyenneGenerale = 0;
            $sommeCoefficients = 0;

            foreach ($matieres as $matiere) {
                $notes = [];

                // Récupère toutes les notes de cet élève pour cette matière et ce trimestre
                foreach (['d1', 'd2', 'mi', 'dh'] as $type) {
                    $note = $this->getNoteForEvaluation($matiere, $evaluationsMap[$type], $trimestre, $eleve);
                    if ($note && $note->getNote() != 0) {
                        $notes[] = $note->getNote();
                    }
                }

                if (count($notes) === 0) continue;

                $coef = $this->classesMatieresRepository->findCoefficientByMatiere($matiere)[0]["coefficient"];
                $moyenneMatiere = array_sum($notes) / count($notes);

                $moyenneParMatiere[$matiere->getId()] = round($moyenneMatiere, 2);
                $moyenneCoef[$matiere->getId()] = round($moyenneMatiere * $coef, 2);

                $moyenneGenerale += $moyenneMatiere * $coef;
                $sommeCoefficients += $coef;
                $moyennesParMatiere[$matiere->getId()][$id] = $moyenneMatiere;
            }

            $moyenneGenerale = $sommeCoefficients > 0 ? round($moyenneGenerale / $sommeCoefficients, 2) : 0;

            $results[$id] = [
                'moyenneParMatiere' => $moyenneParMatiere,
                'moyenneCoef' => $moyenneCoef,
                'moyenneGenerale' => $moyenneGenerale,
            ];
        }

        return [$results, $moyennesParMatiere];
    }

    /**
     * 🔸 Calcule la moyenne annuelle (moyenne des 3 trimestres)
     */
    public function calculateAnnuelle(Classes $classe, array $eleves): array
    {
        [$t1] = $this->calculateTrimestre($classe, $eleves, 1);
        [$t2] = $this->calculateTrimestre($classe, $eleves, 2);
        [$t3] = $this->calculateTrimestre($classe, $eleves, 3);

        $moyennesAnnuelles = [];

        foreach ($eleves as $eleve) {
            $id = $eleve->getId();
            $total = 0;
            $nb = 0;

            foreach ([$t1, $t2, $t3] as $res) {
                if (isset($res[$id]['moyenneGenerale'])) {
                    $total += $res[$id]['moyenneGenerale'];
                    $nb++;
                }
            }

            $moyennesAnnuelles[$id] = $nb > 0 ? round($total / $nb, 2) : 0;
        }

        return $moyennesAnnuelles;
    }

    /**
     * ⚖️ Détermine la décision de passage selon une moyenne donnée
     */
    public function getDecisionPassage(float $moyenne, float $seuil = 10): string
    {
        if ($moyenne >= $seuil) {
            return 'passe';
        } elseif ($moyenne >= $seuil - 1) {
            return 'conseil'; // à décider manuellement
        } else {
            return 'redouble';
        }
    }

    /**
     * 🔍 Récupère une note pour une évaluation donnée
     */
    private function getNoteForEvaluation($matiere, $evaluation, int $trimestre, Eleves $eleve)
    {
        return $this->notesRepository->findOneBy([
            'matiere' => $matiere,
            'evaluation' => $evaluation,
            'Trimestre' => $trimestre,
            'eleve' => $eleve,
        ]);
    }
}
