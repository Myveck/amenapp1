<?php

namespace App\Service;

use App\Entity\Classes;
use App\Entity\Eleves;
use App\Repository\ClassesMatieresRepository;
use App\Repository\ClassesRepository;
use App\Repository\EvaluationsRepository;
use App\Repository\ExaminationsRepository;
use App\Repository\NotesRepository;

class BulletinManager2
{
    private ClassesMatieresRepository $classesMatieresRepository;
    private ExaminationsRepository $examinationsRepository;
    private NotesRepository $notesRepository;
    private EvaluationsRepository $evaluationsRepository;
    private ClassesRepository $classesRepository;

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

    public function calculateTrimestre($classeId, $trimestre)
    {
        $classe = $this->classesRepository->find($classeId);
        $cMatieres = $this->classesMatieresRepository->findBy(['classe' => $classe,]);
        $examination = $this->examinationsRepository->findBy([
            'classe' => $classe,
            'trimestre' => $trimestre,
        ]);
    }
  }