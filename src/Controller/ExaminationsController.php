<?php

namespace App\Controller;

use App\Entity\Examinations;
use App\Form\ExaminationsType;
use App\Repository\ClassesMatieresRepository;
use App\Repository\ClassesRepository;
use App\Repository\ElevesRepository;
use App\Repository\EvaluationsRepository;
use App\Repository\ExaminationsRepository;
use App\Repository\MatieresRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/examinations')]
final class ExaminationsController extends AbstractController
{
    #[Route(name: 'app_examinations_index', methods: ['GET'])]
    public function index(Request $request, ExaminationsRepository $examinationsRepository, ClassesRepository $classesRepository, MatieresRepository $matieresRepository, EvaluationsRepository $evaluationsRepository, ClassesMatieresRepository $classesMatieresRepository): Response
    {
        $matieres = "";
        $trimestre = "";
        $examinations = $examinationsRepository->findAll();

        $classes = $classesRepository->findAll();
        $allClasses = $classesRepository->findAll();
        $evaluations = $evaluationsRepository->findAll();

        $classeId =  $request->get('classe');
        $matiereId =  $request->get('matiere');
        $evaluationId =  $request->get('evaluation');
        $trimestre = intval($request->get('trimestre'));

        // Initialisation des filtres de recherche
        $filtres = [];

        // Si une classe est spécifiée (autre que "all")
        if ($classeId != "all" and $classeId != null) {
            $classe = $classesRepository->findOneBy(["id" => $classeId]);
            if (!$classe) {
                // Gérer le cas où la classe n'existe pas
                $this->addFlash('error', 'Classe introuvable.');
                return $this->redirectToRoute('app_examinations_nouveau');
            }
            $filtres["classe"] = $classe;

            // Récupérer les matières associées à la classe
            $cMatieres = $classesMatieresRepository->findMatiereByClasse($classe);
            $matieres = [];
            foreach ($cMatieres as $cMatiere) {
                $matieres[] = $cMatiere->getMatiere();
            }
        }

        // Si une matière est spécifiée (autre que "all")
        if ($matiereId != "all" and $matiereId != null) {
            $matiere = $matieresRepository->findOneBy(["id" => $matiereId]);
            if (!$matiere) {
                // Gérer le cas où la matière n'existe pas
                $this->addFlash('error', 'Matière introuvable.');
                return $this->redirectToRoute('app_examinations_nouveau');
            }
            $filtres["matiere"] = $matiere;
        }

        // Si une évaluation est spécifiée (autre que "all")
        if ($evaluationId != "all" and $evaluationId != null) {
            $evaluation = $evaluationsRepository->findOneBy(["id" => $evaluationId]);
            if (!$evaluation) {
                // Gérer le cas où l'évaluation n'existe pas
                $this->addFlash('error', 'Évaluation introuvable.');
                return $this->redirectToRoute('app_examinations_nouveau');
            }
            $filtres["evaluation"] = $evaluation;
        }

        if ($trimestre != "all" and $trimestre != null) {
            $filtres["trimestre"] = $trimestre;
        }

        // Récupération des examens en fonction des filtres
        $examinations = $examinationsRepository->findBy($filtres, ['id' => 'desc']);


        return $this->render('examinations/index.html.twig', [
            'examinations' => $examinations,
            'classes' => $classes,
            'matieres' => $matieres,
            'evaluations' => $evaluations,
            'classeActive' => $classeId,
            'evaluationActive' => $evaluationId,
            'matiereActive' => $matiereId,
            'trimestreActive' => $trimestre,
            'allClasses' => $allClasses,
        ]);
    }

    #[Route('/new', name: 'app_examinations_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $examination = new Examinations();
        $form = $this->createForm(ExaminationsType::class, $examination);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($examination);
            $entityManager->flush();

            return $this->redirectToRoute('app_examinations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('examinations/new.html.twig', [
            'examination' => $examination,
            'form' => $form,
        ]);
    }

    #[Route('/nouveau', name: 'app_examinations_nouveau', methods: ['GET', 'POST'])]
    public function examinationNew(Request $request, EvaluationsRepository $evaluationsRepository, ClassesRepository $classesRepository, ClassesMatieresRepository $classesMatieresRepository)
    {
        $classe = $classesRepository->findOneBy(["id" => $request->get("oneClasse")]);
        $cMatieres = $classesMatieresRepository->findMatiereByClasse($classe);
        $matieres = [];

        foreach ($cMatieres as $cMatiere) {
            $matieres[$cMatiere->getMatiere()->getId()] = $cMatiere->getMatiere();
        }

        $evaluations = $evaluationsRepository->findAll();
        return $this->render('examinations/nouveau.html.twig', [
            'classe' => $classe,
            'evaluations' => $evaluations,
            'matieres' => $matieres,
        ]);
    }

    #[Route('/create', name: 'app_examinations_create')]
    public function create(Request $request, ExaminationsRepository $examinationsRepository, ClassesRepository $classesRepository, EvaluationsRepository $evaluationsRepository, MatieresRepository $matieresRepository, EntityManagerInterface $entityManager)
    {
        $date_examen = new DateTime($request->get("date_examen"));
        $examination = new Examinations();
        $classe = $classesRepository->findOneBy(["id" => $request->get('classe')]);
        $matiere = $matieresRepository->findOneBy(["id" => $request->get('matiere')]);
        $evaluation = $evaluationsRepository->findOneBy(["id" => $request->get("evaluation")]);
        $matieres = $request->get("matieres");

        foreach ($matieres as $oneMatiere) {
            $examination = new Examinations();
            $one = $matieresRepository->findOneBy(['id' => $oneMatiere]);
            $verif = $examinationsRepository->findOneBy([
                'classe' => $classe,
                'evaluation' => $evaluation,
                'matiere' => $one,
                'trimestre' => intval($request->get('trimestre')),
            ]);
            if (!$verif) {
                $examination->setClasse($classe);
                $examination->setMatiere($one);
                $examination->setEvaluation($evaluation);
                $examination->setDateExamination($date_examen);
                $examination->setTrimestre(intval($request->get('trimestre')));
            } else {
                $this->addFlash('warning', 'Cette examin existe déjà');
                return $this->redirectToRoute('app_examinations_create_notes', [
                    'examination' => $verif->getId(),
                ]);
            }
            $entityManager->persist($examination);
        }


        $entityManager->flush();

        $this->addFlash('success', 'L\'examin a bien été créé');
        return $this->redirectToRoute('app_examinations_create_notes', [
            'examination' => intval($matieres[0]),
        ]);
    }


    #[Route('/matieres/{classeId}', name: 'app_examinations_matieres')]
    public function getMatieres($classeId, Request $request, ClassesRepository $classesRepository, ClassesMatieresRepository $classesMatieresRepository, ExaminationsRepository $examinationsRepository)
    {
        $classe = $classesRepository->findOneBy(["id" => $classeId]);
        $matieres = $classesMatieresRepository->findBy(["classe" => $classe]);

        $matiereArray = [];

        foreach ($matieres as $matiere) {
            $matiereArray[] = [
                'id' => $matiere->getMatiere()->getId(),
                'nom' => $matiere->getMatiere()->getNom(),
            ];
        }

        return new JsonResponse($matiereArray);
    }

    #[Route('/{examination}/create', name: 'app_examinations_create_notes', methods: ['POST', 'GET'])]
    public function createNote(request $request, ExaminationsRepository $examinationsRepository, ElevesRepository $elevesRepository): Response
    {
        $examination = $examinationsRepository->findOneBy(["id" => intval($request->get("examination"))]);

        $eleves = $elevesRepository->findBy(["classe" => $examination->getClasse()]);

        $notes = $examination->getNote();

        return $this->render('examinations/notes.html.twig', [
            "eleves" => $eleves,
            "examination" => $examination,
            "notes" => $notes,
        ]);
    }

    #[Route('/{id}', name: 'app_examinations_show', methods: ['GET'])]
    public function show(Examinations $examination): Response
    {
        return $this->render('examinations/show.html.twig', [
            'examination' => $examination,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_examinations_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Examinations $examination, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ExaminationsType::class, $examination);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_examinations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('examinations/edit.html.twig', [
            'examination' => $examination,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_examinations_delete', methods: ['POST'])]
    public function delete(Request $request, Examinations $examination, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $examination->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($examination);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_examinations_index', [], Response::HTTP_SEE_OTHER);
    }
}
