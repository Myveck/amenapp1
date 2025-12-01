<?php

namespace App\Service;

use App\Entity\Notes;
use App\Repository\AnneeScolaireRepository;
use App\Repository\ClassesMatieresRepository;
use App\Repository\ClassesRepository;
use App\Repository\EvaluationsRepository;
use App\Repository\ExaminationsRepository;
use App\Repository\InscriptionRepository;
use App\Repository\MatieresRepository;
use App\Repository\NotesRepository;
use Doctrine\ORM\EntityManagerInterface;

class ExaminationManager
{
    public function __construct(
        private ExaminationsRepository  $examinationsRepository,
        private EvaluationsRepository $evaluationRepository,
        private NotesRepository $notesRepository,
        private InscriptionRepository $inscriptionRepository,
        private EntityManagerInterface $entityManager,
        private ClassesRepository $classeRepo,
        private ClassesMatieresRepository $classesMatieresRepository,
        private InscriptionRepository $inscriptionRepo,
        private NotesRepository $notesRepo,
        private AnneeScolaireRepository $anneeScolaireRepo,
        private MatieresRepository $matieresRepository,
    ){}

    public function createExamination($examination, $d1, $d2, $mi, $dh)
    {
        $erreur = [];
        $allNotes = [$d1, $d2, $mi, $dh];
        $examination = $this->examinationsRepository->find($examination);

        $i = 33; // correspond à l'ID d'évaluation (1 => D1, 2 => D2, etc.)

        foreach ($allNotes as $notes) {
            if (!$notes) {
                $i++;
                continue; // ignore si la série est vide
            }

            foreach ($notes as $id => $noteEleve) {
                $evaluation = $this->evaluationRepository->find($i);
                $eleve = $this->inscriptionRepository->findEleveActif($id);

                if (!$eleve) {
                    $erreur['warning'] = "L'élève avec l'ID $id n'existe pas";
                    continue; // on continue sans interrompre tout le traitement
                }

                $note = $this->notesRepository->findOneBy([
                    'eleve' => $eleve,
                    'examinations' => $examination,
                    'evaluation' => $evaluation
                ]);

                if ($note) {
                    $note->setNote($noteEleve);
                } else {
                    $note = new Notes();
                    $note->setNote($noteEleve ?: 0.0);
                    $note->setDateEvaluation($examination->getDateExamination());
                    $note->setEleveId($eleve);
                    $note->setEvaluation($evaluation);
                    $note->setExaminations($examination);
                    $this->entityManager->persist($note);
                }
            }

            $i++;
        }

        $this->entityManager->flush();

        return [
            'erreur' => $erreur,
            'classe' => $examination->getClasse()->getId(),
        ];
    }

    public function findByTrimestre(int $matiereId, int $classeId, int $examinationId)
    {
        $classe = $this->classeRepo->find($classeId);
        $examination = $this->examinationsRepository->findOneBy(['id' => $examinationId]);
        $classeMatiere = $this->classesMatieresRepository->findOneBy(['matiere'=> $matiereId]);

        if (!$classeMatiere) {
            $erreur = ['error'=>'Cette matière n\'est pas associée à cette classe.'];
        }
        $matiere = $classeMatiere->getMatiere();

        $eleves = $this->inscriptionRepo->findElevesActuelsByClasse($classe);

        $evaluations = $this->evaluationRepository->findAll();

        $notes = [];

        foreach ($evaluations as $evaluation) {
            $nom = $evaluation->getNom();

            if (in_array($nom, ['Devoir 1', 'Devoir 2', 'MI', 'DH'])) {
                $notes[$nom] = $this->notesRepo->findBy([
                    "examinations" => $examination,
                    "evaluation" => $evaluation,
                ]);
            }
        }

        // $notesD2 = $this->notesRepo->findBy([
        //     "examinations" => $examination,
        //     "evaluation" => 'D2',
        // ]);
        // $notesMi = $this->notesRepo->findBy([
        //     "examinations" => $examination,
        //     "evaluation" => 'MI',
        // ]);
        // $notesDh = $this->notesRepo->findBy([
        //     "examinations" => $examination,
        //     "evaluation" => 'DH',
        // ]);

        $evaluations = [
            'd1' => $notes['Devoir 1'],
            'd2' => $notes['Devoir 2'],
            'mi' => $notes['MI'],
            'dh' => $notes['DH'],
        ];

        return [
            "eleves" => $eleves,
            "classe" => $classe,
            "matiere" => $matiere,
            "evaluations" => $evaluations,
            "examination" => $examination,
        ];
    }

    public function filter($classeId, $matiereId, $evaluationId, $trimestreParam)
    {
        $maxLimit = 15;
        $matieres = [];
        $examinationsActuelle = $this->examinationsRepository->findByAnneActuelle();

        $anneScolaire = $this->anneeScolaireRepo->findOneBy(["actif" => true]);
        $classes = $this->classeRepo->findBy(["annee_scolaire" => $anneScolaire], ["classeOrder" => "ASC"]);
        $evaluations = $this->evaluationRepository->findAll();


        $filtres = [];
        if ($classeId && $classeId !== "all") {
            $classe = $this->classeRepo->find($classeId);
            if (!$classe) {
                $erreur = ['error','Classe introuvable.'];
                return $erreur;
            }
            $filtres["classe"] = $classe;
            $matieres = $this->classesMatieresRepository->findMatiereByClasse($classe);
            $maxLimit = 30;
        }

        if ($matiereId && $matiereId !== "all") {
            $matiere = $this->matieresRepository->find($matiereId);
            if (!$matiere) {
                $erreur = ['error', 'Matière introuvable.'];
                return $erreur;
            }
            $filtres["matiere"] = $matiere;
        }

        if ($evaluationId && $evaluationId !== "all") {
            $evaluation = $this->evaluationRepository->find($evaluationId);
            if (!$evaluation) {
                $erreur = ['error','evaluation introuvable.'];
                return $erreur;
            }
            $filtres["evaluation"] = $evaluation;
        }

        if ($trimestreParam && $trimestreParam !== "all") {
            $filtres["trimestre"] = intval($trimestreParam);
        }

        $examinationsFiltre = $this->examinationsRepository->findBy($filtres, ['id' => 'desc'], $maxLimit);

        $idsActuelles = array_map(fn($e) => $e->getId(), $examinationsActuelle);
        $examinations = array_filter($examinationsFiltre, fn($e) => in_array($e->getId(), $idsActuelles));

        return [
            'examinations' => $examinations,
            'classes' => $classes,
            'matieres' => $matieres,
            'evaluations' => $evaluations,
            'classeActive' => $classeId,
            'evaluationActive' => $evaluationId,
            'matiereActive' => $matiereId,
            'trimestreActive' => $trimestreParam,
            'allClasses' => $classes,
        ];
    }

}