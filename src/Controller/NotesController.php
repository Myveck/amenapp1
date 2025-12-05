<?php

namespace App\Controller;

use App\Entity\Notes;
use App\Repository\ClassesRepository;
use App\Repository\EcolesRepository;
use App\Repository\InscriptionRepository;
use App\Repository\NotesRepository;
use App\Service\BulletinManager2;
use App\Service\ExaminationManager;
use App\Service\FicheExcelManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notes')]
final class NotesController extends AbstractController
{
    #[Route(name: 'app_notes_index', methods: ['GET'])]
    public function index(Request $request, NotesRepository $notesRepository, ClassesRepository $classesRepository): Response
    {
        $trie = $request->get("trie");

        if (!$trie) {
            $trie = "all";
        }

        return $this->render('notes/index.html.twig', [
            'notes' => $notesRepository->findByAnneeActuel(),
            'classes' => $classesRepository->findByAnneeActuelleOrdered(),
            'trie' => $trie,
        ]);
    }


    #[Route('/moyennes', name: 'app_notes_moyennes', methods: ['GET'])]
    public function showMoyennes(Notes $note): Response
    {
        return $this->render('notes/moyennes.html.twig', [
            'note' => $note,
        ]);
    }


    #[Route('/create/exam/{examination}', name: 'app_notes_create_exam', methods: ['GET'])]
    public function saveNotes(Request $request, ExaminationManager $examinationManager) 
    {
        $d1 = $request->get('D1');
        $d2 = $request->get('D2');
        $dh = $request->get('DH');
        $mi = $request->get('MI');
        $examinationId = $request->get('examination');

        $result = $examinationManager->createExamination($examinationId, $d1, $d2, $mi, $dh,);

        $this->addFlash("success", "Les notes ont bien été enregistrées.");
        return $this->redirectToRoute("app_examinations_index", [
            'classe' => $result['classe'],
        ]);
    }

    #[Route('/{id}', name: 'app_notes_delete', methods: ['POST'])]
    public function delete(Request $request, Notes $note, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $note->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($note);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_notes_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/bulletins/trimestre/{classeId}', name: 'app_notes_bulletins_trimestre')]
    public function showBulletin(
        int $classeId,
        BulletinManager2 $bulletinManager,
        Request $request
    ): Response {
        $trimestre = $request->get('trimestre');
        $resultats = $bulletinManager->calculateTrimestre($classeId, $trimestre);
        $bilanClasse = $bulletinManager->calculateBilan($resultats[0]);

        $firstTrimestre = '';
        $secondTrimestre = '';
        if($trimestre == 3) {
            $firstTrimestre = $bulletinManager->calculateTrimestre($classeId, 1)[0];
            $secondTrimestre = $bulletinManager->calculateTrimestre($classeId, 2)[0];
        }

        // dd($firstTrimestre);

        // Informations globales de l’établissement
        $etablissement = [
            'nom' => 'CPEG AMEN',
            'adresse' => '07 BP 155 Cotonou',
            'telephone' => '21 35 08 90 / 66 43 14 14',
            'devise' => 'Discipline - Travail - Succès',
            'logo' => 'images/logo-amen.png', // optionnel
        ];

        return $this->render('/notes/classe.html.twig', [
            'resultats' => $resultats[0],
            'classe' => $resultats[1],
            'etablissement' => $etablissement,
            'bilanClasse' => $bilanClasse,
            'trimestre' => $trimestre,
            'firstTrimestre' => $firstTrimestre,
            'secondTrimestre' => $secondTrimestre,
        ]);
    }

    #[Route('/fiche-retrait/trimestre/{classeId}', name: 'app_notes_fiche-retrait_trimestre')]
    public function showFicheRetrait(int $classeId, Request $request, BulletinManager2 $bulletinManager, EcolesRepository $ecolesRepository): Response {
        $ecole = $ecolesRepository->find(1);
        $trimestre = $request->get('trimestre');
        $resultats = $bulletinManager->calculateTrimestre($classeId, $trimestre);
        $bilanClasse = $bulletinManager->calculateBilan($resultats[0]);

        return $this->render('/notes/retrait_bulletins.html.twig', [
            'classe' => $resultats[1],
            'rangGeneral' => $bilanClasse['moyenneClasse'],
            'trimestre' => $trimestre,
            'ecole' => $ecole,
            'eleves' => $resultats[2],
            'sommeCoefficients' => $resultats[3],
            'matiereCoef' => 1,
            'results' => $resultats[0],

        ]);
    }

    #[Route('/fiche-verification/trimestre/{classeId}', name: 'app_notes_fiche-verification_trimestre')]
    public function showFicheVerification(int $classeId, Request $request, BulletinManager2 $bulletinManager, EcolesRepository $ecolesRepository): Response {
        $ecole = $ecolesRepository->find(1);
        $trimestre = $request->get('trimestre');
        $resultats = $bulletinManager->calculateTrimestre($classeId, $trimestre);
        $bilanClasse = $bulletinManager->calculateBilan($resultats[0]);

        return $this->render('notes/verif_bulletins.html.twig', [
            'classe' => $resultats[1],
            'effectif' => $resultats[3],
            'matieres' => $resultats[4],
            'eleves' => $resultats[3],
            'moyenneGForte' => $bilanClasse['moyenneForte'],
            'moyenneGFaible' => $bilanClasse['moyenneFaible'],
            'moyenneGClasse' => $bilanClasse['moyenneClasse'],
            'trimestre' => $trimestre,
            'ecole' => $ecole,
            'success' => $bilanClasse['admis'],
            'fail' => $bilanClasse['echoues'],
            'tauxReussite' => $bilanClasse['tauxAdmis'],
            'results' => $resultats[0],
        ]);
    }

    #[Route('/fiche-excel/trimestre/{classeId}', name: 'app_notes_fiche-excel_trimestre')]
    public function showFicheExcel(int $classeId, Request $request, BulletinManager2 $bulletinManager, FicheExcelManager $ficheExcelManager): Response {
        $trimestre = $request->get('trimestre');
        $results = $bulletinManager->calculateTrimestre($classeId, $trimestre);
       
        $response = $ficheExcelManager->exportExcel($results, $trimestre);

        return $response;
            
    }

    #[Route('/eleves/{classeId}', name: 'app_notes_eleves_classe')]
    public function elevesParClasse(int $classeId, InscriptionRepository $inscriptionRepo): JsonResponse
    {
        $inscriptions = $inscriptionRepo->findBy(['classe' => $classeId]);

        $eleves = [];

        foreach($inscriptions as $inscription) {
            $eleve[] = $inscription->getEleve();
        }

        $data = [];

        foreach ($eleves as $eleve) {
            $data[] = [
                'id' => $eleve->getId(),
                'nom' => $eleve->getNom().' '.$eleve->getPrenom(),
                'formAction' => $this->generateUrl('app_notes_bulletins_trimestre', [
                    'eleveId' => $eleve->getId()
                ])
            ];
        }

        return new JsonResponse(['eleves' => $data]);
    }

}
