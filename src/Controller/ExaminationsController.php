<?php

namespace App\Controller;

use App\Entity\Examinations;
use App\Form\ExaminationsType;
use App\Repository\AnneeScolaireRepository;
use App\Repository\ClassesMatieresRepository;
use App\Repository\ClassesRepository;
use App\Repository\ElevesRepository;
use App\Repository\EvaluationsRepository;
use App\Repository\ExaminationsRepository;
use App\Repository\InscriptionRepository;
use App\Repository\MatieresRepository;
use App\Repository\NotesRepository;
use App\Service\ExaminationManager;
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
    public function index(Request $request, ExaminationsRepository $examinationsRepository, ClassesRepository $classesRepository, MatieresRepository $matieresRepository, EvaluationsRepository $evaluationsRepository, ClassesMatieresRepository $classesMatieresRepository, AnneeScolaireRepository $anneeSR, InscriptionRepository $inscriptionRepo, ExaminationManager $examManager): Response
    {
        $classeId = $request->get('classe');
        $matiereId = $request->get('matiere');
        $evaluationId = $request->get('evaluation');
        $trimestreParam = $request->get('trimestre');

        $data = $examManager->filter($classeId, $matiereId, $evaluationId, $trimestreParam);

        if(in_array('error', $data)){
            $this->addFlash('error', end($data));
            return $this->redirectToRoute('app_examinations_index');
        }

        return $this->render('examinations/index.html.twig', $data);
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
    public function examinationNew(Request $request, ClassesRepository $classesRepository, ClassesMatieresRepository $classesMatieresRepository, AnneeScolaireRepository $anneeSR)
    {
        $classe = $classesRepository->findOneBy(["id" => $request->get("oneClasse")]);
        $matieres = $classesMatieresRepository->findMatiereByClasse($classe);

        $evaluations = $anneeSR->findOneBy(["actif" => 1])->getEvaluations();
        return $this->render('examinations/nouveau.html.twig', [
            'classe' => $classe,
            'evaluations' => $evaluations,
            'matieres' => $matieres,
        ]);
    }

    #[Route('/create', name: 'app_examinations_create')]
    public function create(Request $request, ExaminationsRepository $examinationsRepository, ClassesRepository $classesRepository, EvaluationsRepository $evaluationsRepository, MatieresRepository $matieresRepository, EntityManagerInterface $entityManager, AnneeScolaireRepository $anneeScolaireRepo)
    {
        $matiereId = $request->get("matieres");
        $trimestre = intval($request->get('trimestre'));
        
        $dateExamen = new \DateTime($request->get("date_examen"));
        $anneeScolaire = $anneeScolaireRepo->findOneBy(["actif" => true]);
        $classe = $classesRepository->find($request->get('classe'));

        if (!$classe || !$anneeScolaire || empty($matiereId)) {
            $this->addFlash('error', 'Informations manquantes ou invalides.');
            return $this->redirectToRoute('app_examinations_nouveau');
        }

        // Charger toutes les matières d'un coup
        $matieres = $matieresRepository->findBy(['id' => $matiereId]);

        // Vérifier les examens déjà existants pour cette classe / année / trimestre
        $dejaExistants = $examinationsRepository->findBy([
            'classe' => $classe,
            'annee_scolaire' => $anneeScolaire,
            'trimestre' => $trimestre,
        ]);

        // Créer un index local (matière_id => true)
        $existantsIndex = [];
        foreach ($dejaExistants as $exam) {
            $existantsIndex[$exam->getMatiere()->getId()] = true;
        }

        $nouveauxExamens = [];
        foreach ($matieres as $matiere) {
            $matiereId = $matiere->getId();

            // Si l'examen existe déjà pour cette matière, on ignore
            if (isset($existantsIndex[$matiereId])) {
                continue;
            }

            // Création du nouvel examen
            $exam = (new Examinations())
                ->setClasse($classe)
                ->setMatiere($matiere)
                ->setDateExamination($dateExamen)
                ->setAnneeScolaire($anneeScolaire)
                ->setTrimestre($trimestre);

            $entityManager->persist($exam);
            $nouveauxExamens[] = $exam;
        }

        // Enregistrement
        if ($nouveauxExamens) {
            $entityManager->flush();
            $this->addFlash('success', sprintf('%d examen(s) créé(s) avec succès.', count($nouveauxExamens)));
        } else {
            $this->addFlash('warning', 'Tous les examens existent déjà.');
        }

        // Redirection vers le premier examen créé (ou existant)
        $premiereMatiere = $matieres[0];
        $exam = $examinationsRepository->findOneBy([
            'matiere' => $premiereMatiere,
            'classe' => $classe,
            'trimestre' => $trimestre,
        ]);

        return $this->redirectToRoute('app_examinations_create_notes', [
            'examinationId' => $exam ? $exam->getId() : null,
            'matiereId' => $premiereMatiere->getId(),
            'trimestre' => $trimestre,
            'classeId' => $classe->getId(),
        ]);

    }


    #[Route('/matieres/{classeId}', name: 'app_examinations_matieres')]
    public function getMatieres($classeId, ClassesRepository $classesRepository, ClassesMatieresRepository $classesMatieresRepository)
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

    #[Route('/{examinationId}/{matiereId}/{trimestre}/{classeId}/create', name: 'app_examinations_create_notes', methods: ['POST', 'GET'])]
    public function createNote(request $request, ExaminationManager $examinationManager): Response
    {
        $matiereId = intval($request->get('matiereId'));
        $classeId = intval($request->get("classeId"));
        $examinationId = intval($request->get("examinationId"));

        $data = $examinationManager->findByTrimestre($matiereId, $classeId, $examinationId);

        return $this->render('examinations/notes.html.twig', $data);

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
