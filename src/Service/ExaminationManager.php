<?php

namespace App\Service;

use App\Entity\Notes;
use App\Repository\EvaluationsRepository;
use App\Repository\ExaminationsRepository;
use App\Repository\InscriptionRepository;
use App\Repository\NotesRepository;
use Doctrine\ORM\EntityManagerInterface;

class ExaminationManager
{
  public function __construct(
    private ExaminationsRepository  $examinationsRepository,
    private EvaluationsRepository $evaluationRepository,
    private NotesRepository $notesRepository,
    private InscriptionRepository $inscriptionRepository,
    private EntityManagerInterface $entityManager
  ){}

  public function createExamination($examination, $d1, $d2, $mi, $dh)
  {
    $erreur = [];
    $allNotes = [$d1, $d2, $mi, $dh];
    $examination = $this->examinationsRepository->find($examination);

        $i = 1; // correspond à l'ID d'évaluation (1 => D1, 2 => D2, etc.)

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
}