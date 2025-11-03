<?php

namespace App\Controller;

use App\Entity\Eleves;
use App\Entity\Notes;
use App\Form\NotesType;
use App\Repository\ClassesMatieresRepository;
use App\Repository\ClassesRepository;
use App\Repository\EcolesRepository;
use App\Repository\ElevesRepository;
use App\Repository\EvaluationsRepository;
use App\Repository\ExaminationsRepository;
use App\Repository\InscriptionRepository;
use App\Repository\NotesRepository;
use App\Service\BulletinManager2;
use App\Service\ExaminationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Zip;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function PHPUnit\Framework\isEmpty;

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
        $d1 = $request->get('d1');
        $d2 = $request->get('d2');
        $dh = $request->get('dh');
        $mi = $request->get('mi');
        $examinationId = $request->get('examination');

        $result = $examinationManager->createExamination($examinationId, $d1, $d2, $mi, $dh,);

        $this->addFlash("success", "Les notes ont bien été enregistrées.");
        return $this->redirectToRoute("app_examinations_index", [
            'classe' => $result['classe'],
        ]);
    }


    #[Route('/bulletin', name: 'app_notes_bulletins', methods: ['GET'])]
    public function choiceTrimestre(): Response
    {
        return $this->render('notes/choice_bulletin_trimestre.html.twig');
    }

    #[Route('/{id}', name: 'app_notes_show', methods: ['GET'])]
    public function show(Notes $note): Response
    {
        return $this->render('notes/show.html.twig', [
            'note' => $note,
        ]);
    }

    #[Route('/{id}/new', name: 'app_notes_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Eleves $eleve, EntityManagerInterface $entityManager): Response
    {
        $note = new Notes();
        $note->setEleveId($eleve);

        $form = $this->createForm(NotesType::class, $note, [
            'allow_extra_fields' => $note->getEleveId()->getClasseActuelle()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($note);
            $entityManager->flush();
            dd($note);


            return $this->redirectToRoute('app_notes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('notes/add.html.twig', [
            'note' => $note,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_notes_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Notes $note, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NotesType::class, $note, [
            'allow_extra_fields' => $note->getEleveId()->getClasseActuelle()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_notes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('notes/edit.html.twig', [
            'note' => $note,
            'form' => $form,
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

    // Fonction utilitaire pour récupérer les notes à partir de la relation avec note
    private function getNoteForEvaluation($notesRepository, $examinationsRepository, $matiere, $evaluation, $trimestre, $eleve)
    {
        $examination = $examinationsRepository->findOneBy([
            "matiere" => $matiere,
            "evaluation" => $evaluation,
            "trimestre" => $trimestre,
        ]);

        if ($examination) {
            // Récupérer la note liée à l'examination pour cet élève
            return $notesRepository->findOneBy([
                "examinations" => $examination,
                "eleve" => $eleve,
            ]);
        }

        return null;
    }

    private function calculateBulletins(
        $classe,
        $eleves,
        $trimestre,
        ClassesMatieresRepository $classesMatieresRepository,
        ExaminationsRepository $examinationsRepository,
        NotesRepository $notesRepository,
        EvaluationsRepository $evaluationsRepository,
    ) {
        // Récupérer les évaluations
        $evaluations = $evaluationsRepository->findBy(["id" => [1, 2, 3, 4]]);
        $evaluationsMap = [
            'd1' => $evaluations[0] ?? null,
            'd2' => $evaluations[1] ?? null,
            'mi' => $evaluations[2] ?? null,
            'dh' => $evaluations[3] ?? null,
        ];

        // Récupérer les matières de la classe
        $matieres = $classesMatieresRepository->findMatiereByClasse($classe);

        // Initialiser les tableaux pour les résultats
        $results = [];
        $moyenneGenerale = [];
        $sommeDesCoefficients = [];
        $notesParEvaluation = [];
        $moyennesParMatiere = [];
        $coefficientParMatiere = [];

        if (!$eleves) {
            $message = ['warning', 'Cette classe ne contient aucun élève'];
        }

        // Calculer les moyennes et les notes pour chaque élève
        foreach ($eleves as $eleve) {
            $moyenneParMatiere = [];
            $moyenneCoef = [];
            $notesParMatiere = [];
            $notesParEvaluation[$eleve->getId()] = [];
            $moyenneGenerale[$eleve->getId()] = 0;
            $sommeDesCoefficients[$eleve->getId()] = 0;

            if (!$matieres) {
                $message = ['warning', 'Cette classe ne contient aucune matière veuillez en ajouter'];
            }
            foreach ($matieres as $matiere) {
                $moyenne = [];

                // Notes par évaluation pour cet élève et cette matière
                $noteD1 = $this->getNoteForEvaluation($notesRepository, $examinationsRepository, $matiere, $evaluationsMap['d1'], $trimestre, $eleve);
                $noteD2 = $this->getNoteForEvaluation($notesRepository, $examinationsRepository, $matiere, $evaluationsMap['d2'], $trimestre, $eleve);
                $noteMi = $this->getNoteForEvaluation($notesRepository, $examinationsRepository, $matiere, $evaluationsMap['mi'], $trimestre, $eleve);
                $noteDh = $this->getNoteForEvaluation($notesRepository, $examinationsRepository, $matiere, $evaluationsMap['dh'], $trimestre, $eleve);

                // Ajout des notes dans le tableau des évaluations par élève et matière
                $notesParEvaluation[$eleve->getId()][$matiere->getId()] = [
                    'd1' => $noteD1 ? $noteD1->getNote() : null,
                    'd2' => $noteD2 ? $noteD2->getNote() : null,
                    'mi' => $noteMi ? $noteMi->getNote() : null,
                    'dh' => $noteDh ? $noteDh->getNote() : null,
                ];

                // Ajout des notes pour calculer la moyenne
                if ($noteD1 && $noteD1->getNote() != 0) $moyenne[] = $noteD1->getNote();
                if ($noteD2 && $noteD2->getNote() != 0) $moyenne[] = $noteD2->getNote();
                if ($noteMi && $noteMi->getNote() != 0) $moyenne[] = $noteMi->getNote();
                if ($noteDh && $noteDh->getNote() != 0) $moyenne[] = $noteDh->getNote();

                // Calcul des notes totales et moyenne par matière
                $notesParMatiere[$matiere->getId()] = array_sum($moyenne);
                $coefficient = $classesMatieresRepository->findCoefficientByMatiere($matiere)[0]["coefficient"];
                $coefficientParMatiere[$matiere->getId()] = $coefficient;

                $noteCount = count($moyenne);
                if ($noteCount > 0) {
                    $moyenneCoef[$matiere->getId()] = round(($notesParMatiere[$matiere->getId()] / $noteCount) * $coefficient, 2);
                    $moyenneParMatiere[$matiere->getId()] = round(($notesParMatiere[$matiere->getId()] / $noteCount), 2);


                    // Ajouter à la moyenne générale et à la somme des coefficients
                    $moyenneGenerale[$eleve->getId()] += $moyenneCoef[$matiere->getId()];
                    $sommeDesCoefficients[$eleve->getId()] += $coefficient;
                    $moyennesParMatiere[$matiere->getId()][$eleve->getId()] = $moyenneCoef[$matiere->getId()];
                }
            }

            // Calcul de la moyenne générale pour l'élève
            if ($sommeDesCoefficients[$eleve->getId()] > 0) {
                $moyenneGenerale[$eleve->getId()] = round($moyenneGenerale[$eleve->getId()] / $sommeDesCoefficients[$eleve->getId()], 2);
            }

            // Stocker les résultats pour cet élève
            $results[$eleve->getId()] = [
                'moyenneParMatiere' => $moyenneParMatiere,
                'moyenneCoef' => $moyenneCoef,
                'moyenneGenerale' => $moyenneGenerale[$eleve->getId()],
                'notesParEvaluation' => $notesParEvaluation[$eleve->getId()],
                'coefficientParMatiere' => $coefficientParMatiere,
            ];
        }

        if (isset($message)) {
            return [$results, $moyennesParMatiere, $message];
        }

        return [$results, $moyennesParMatiere];
    }

    #[Route('/{classe}/{requete}/bulletins', name: 'app_notes_bulletins')]
    public function bulletin(
        Request $request,
        ClassesRepository $classesRepository,
        ExaminationsRepository $examinationsRepository,
        ElevesRepository $elevesRepository,
        ClassesMatieresRepository $classesMatieresRepository,
        EvaluationsRepository $evaluationsRepository,
        NotesRepository $notesRepository,
        EcolesRepository $ecolesRepository,
        InscriptionRepository $inscriptionRepository
    ): Response {
        $classeId = $request->get('classe');
        $trimestre = $request->get('trimestre');
        $classe = $classesRepository->findOneBy(["id" => $classeId]);
        $eleves = $inscriptionRepository->findElevesActuelsByClasse($classe);
        $effectif = count($eleves);
        $ecole = $ecolesRepository->findOneBy(['id' => 1]);
        $matieres = [];
        $matiereCoef = [];
        $results1 = [];
        $results2 = [];
        $sommeCoefficients = 0;

        $cMatieres = $classesMatieresRepository->findByClasse($classe);


        foreach ($cMatieres as $cMatiere) {
            $matieres[$cMatiere->getMatiere()->getId()] = $cMatiere->getMatiere();
            $matiereCoef[$cMatiere->getMatiere()->getId()] = $cMatiere->getCoefficient();
            $sommeCoefficients += $cMatiere->getCoefficient();
        }
        

        // Utiliser la méthode commune pour calculer les résultats
        $results = $this->calculateBulletins(
            $classe,
            $eleves,
            $trimestre,
            $classesMatieresRepository,
            $examinationsRepository,
            $notesRepository,
            $evaluationsRepository
        );

        // // Classification des élèves avec Id comme clé dans un tableau afin de mieux les utiliser dans le twig
        $students = [];
        foreach ($eleves as $un) {
            $students[$un->getId()] = $un;
        }

        // // Classification des moyennes par matière
        $rangParMatiere = [];

        if (isset($results[2])) {

            $this->addFlash($results[2][0], $results[2][1]);
            return $this->redirectToRoute("app_classes_bulletins");
        } else {
            if ($results[1]) {
                $moyennesParMatiere = $results[1];
                foreach ($moyennesParMatiere as $m => $moy) {
                    arsort($moy);
                    $moyennes[$m] = $moy;
                }

                $PlusForteMoyenne = [];
                $PlusFaibleMoyenne = [];

                foreach ($moyennes as $l => $n) {
                    $PlusForteMoyenne[$l] = reset($n);
                    $PlusFaibleMoyenne[$l] = end($n);
                }

                $rangParMatiere = [];
                foreach ($moyennes as $l => $n) {
                    arsort($n);

                    $rang = 1;
                    foreach ($n as $nKey => $nVal) {
                        $rangParMatiere[$l][$nKey] = $rang++;
                    }
                }

                $general = [];
                $rangGeneral = [];
                foreach ($results[0] as $resKey => $resultat) {
                    $general[$resKey] = $resultat["moyenneGenerale"];
                }

                arsort($general);
                $moyenneGForte = reset($general);
                $moyenneGFaible = end($general);

                $moyenneGClasse = round(array_sum($general) / count($eleves), 2);

                $rangG = 1;
                foreach ($general as $elKey => $elMoy) {
                    $rangGeneral[$elKey] = $rangG++;
                }
            } else {
                $this->addFlash('danger', 'Il n\'existe pas de bulletin pour cette classe ou ce trimestre');
                return $this->redirectToRoute("app_classes_bulletins");
            }
        }

        if ($request->get('requete') == 'bulletins') {

            // Si c'est le troisième trimestre, on aurait besoin des résultats des trimestres passé
            if ($trimestre == 3) {
                $results1 = $this->calculateBulletins(
                    $classe,
                    $eleves,
                    1,
                    $classesMatieresRepository,
                    $examinationsRepository,
                    $notesRepository,
                    $evaluationsRepository
                );
                $results2 = $this->calculateBulletins(
                    $classe,
                    $eleves,
                    2,
                    $classesMatieresRepository,
                    $examinationsRepository,
                    $notesRepository,
                    $evaluationsRepository
                );

                if (isset($results1[2])) {

                    $this->addFlash($results[2][0], $results[2][1]);
                    return $this->redirectToRoute("app_classes_bulletins");
                } else {
                    // On ne prend que les moyennes générales de ce trimestre
                    $results1 = $results1[0];
                }

                if (isset($results2[2])) {

                    $this->addFlash($results[2][0], $results[2][1]);
                    return $this->redirectToRoute("app_classes_bulletins");
                } else {
                    $results2 = $results2[0];
                }
            }

            return $this->render('/notes/bulletins.html.twig', [
                'classe' => $classe,
                'effectif' => $effectif,
                'matieres' => $matieres,
                'students' => $students,
                'rangParMatiere' => $rangParMatiere,
                'plusForteMoyenne' => $PlusForteMoyenne,
                'plusFaibleMoyenne' => $PlusFaibleMoyenne,
                'moyenneGForte' => $moyenneGForte,
                'moyenneGFaible' => $moyenneGFaible,
                'rangGeneral' => $rangGeneral,
                'moyenneGClasse' => $moyenneGClasse,
                'trimestre' => $trimestre,
                'ecole' => $ecole,
                'matiereCoef' => $matiereCoef,
                'results1' => $results1,
                'results2' => $results2,
                'results' => $results,  // Contient les moyennes et résultats de chaque élève
            ]);
        } else if ($request->get('requete') == 'verif') {
            foreach ($eleves as $oneEleve) {
                $elevesId[$oneEleve->getId()] = $oneEleve;
            }

            $success = 0;
            $fail = 0;

            foreach ($results[0] as $moy) {
                if ($moy['moyenneGenerale'] < 10) {
                    $fail += 1;
                } else {
                    $success += 1;
                }
            }

            $successRate = round((($success * 100) / $effectif), 2);

            return $this->render('notes/verif_bulletins.html.twig', [
                'classe' => $classe,
                'effectif' => $effectif,
                'matieres' => $matieres,
                'eleves' => $elevesId,
                'moyenneGForte' => $moyenneGForte,
                'moyenneGFaible' => $moyenneGFaible,
                'rangGeneral' => $rangGeneral,
                'moyenneGClasse' => $moyenneGClasse,
                'trimestre' => $trimestre,
                'ecole' => $ecole,
                'success' => $success,
                'fail' => $fail,
                'tauxReussite' => $successRate,
                'results' => $results[0],
            ]);
        } else if ($request->get('requete') == 'retrait') {

            return $this->render('/notes/retrait_bulletins.html.twig', [
                'classe' => $classe,
                'rangGeneral' => $rangGeneral,
                'trimestre' => $trimestre,
                'ecole' => $ecole,
                'eleves' => $students,
                'sommeCoefficients' => $sommeCoefficients,
                'matiereCoef' => $matiereCoef,
                'results' => $results[0],

            ]);
        } 
        else if ($request->get('requete') == 'central') {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->removeSheetByIndex(0); // Remove default empty sheet

            foreach ($matieres as $key => $value) {
                // Create new sheet for each subject
                $sheet = $spreadsheet->createSheet();
                if($value->getNom() == "Allemand/Espagnol"){
                    $sheet->setTitle("Allemand ou Esagnol");
                }
                else{
                    $sheet->setTitle($value->getNom());
                }

                // Write header row
                $sheet->fromArray(['Noms', 'Prénoms', 'Moy.interro', 'Devoir 1', 'Devoir 2'], null, 'A1');

                // Write data rows
                $row = 2;
                foreach ($results[0] as $resKey => $resValue) {
                    foreach ($resValue["notesParEvaluation"] as $noteKey => $noteValue) {
                        if ($key == $noteKey) {
                            $sheet->fromArray([
                                $students[$resKey]->getNom(),
                                $students[$resKey]->getPrenom(),
                                $noteValue['mi'],
                                $noteValue['d1'],
                                $noteValue['d2'],
                            ], null, "A$row");
                            $row++;
                        }
                    }
                }
            }

            // Save the Excel file
            $excelFilename = sys_get_temp_dir() . '/' . $classe->getNom() . '_Trimestre' . $trimestre . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            $writer->save($excelFilename);

            // Send file as response
            $response = new BinaryFileResponse($excelFilename);
            $response->setContentDisposition('attachment', basename($excelFilename));

            // Delete file after sending
            $response->deleteFileAfterSend(true);

            return $response;
        }
        
    }

    #[Route('/{classe}/retrait/bulletins', name: 'app_notes_retrait_bulletins', methods: ['GET', 'POST'])]
    public function retraitBulletin(
        Request $request,
        ClassesRepository $classesRepository,
        ExaminationsRepository $examinationsRepository,
        ElevesRepository $elevesRepository,
        NotesRepository $notesRepository,
        ClassesMatieresRepository $classesMatieresRepository,
        EvaluationsRepository $evaluationsRepository,
        EcolesRepository $ecolesRepository,
    ): Response {

        $classeId = $request->get('classe');
        $trimestre = $request->get('trimestre');
        $classe = $classesRepository->findOneBy(["id" => $classeId]);
        $eleves = $elevesRepository->findBy(["classe" => $classe], ['nom' => 'asc']);
        $ecole = $ecolesRepository->findOneBy(['id' => 1]);
        $elevesId = [];
        $matieres = [];
        $coefficients = 0;

        if (!$eleves) {
            $this->addFlash('warning', 'Cette classe est vide, il n\'y a aucun élève');
            return $this->redirectToRoute("app_classes_bulletins");
        }

        $cMatieres = $classesMatieresRepository->findMatiereByClasse($classe);


        if ($cMatieres) {
            foreach ($cMatieres as $cMatiere) {
                $matieres[$cMatiere->getMatiere()->getId()] = $cMatiere->getMatiere();
                $coefficients += $cMatiere->getCoefficient();
            }

            foreach ($eleves as $oneEleve) {
                $elevesId[$oneEleve->getId()] = $oneEleve;
            }

            // Calcul des résultats
            $results = $this->calculateBulletins(
                $classe,
                $eleves,  // Passer un tableau avec un seul élève
                $trimestre,
                $classesMatieresRepository,
                $examinationsRepository,
                $notesRepository,
                $evaluationsRepository
            );

            if (isset($results[2])) {
                $this->addFlash($results[2][0], $results[2][1]);
                return $this->redirectToRoute("app_classes_bulletins");
            } else {
                $general = [];
                $rangGeneral = [];
                foreach ($results[0] as $resKey => $resultat) {
                    $general[$resKey] = $resultat["moyenneGenerale"];
                }

                arsort($general);

                $rangG = 1;
                foreach ($general as $elKey => $elMoy) {
                    $rangGeneral[$elKey] = $rangG++;
                }
            }
        } else {
            $this->addFlash('warning', 'Il n\existe pas de matière pour cette classe ');
            return $this->redirectToRoute("app_classes_bulletins");
        }

        return $this->render('notes/retrait_bulletins.html.twig', [
            'results' => $results[0],
            'ecole' => $ecole,
            'rangGeneral' => $rangGeneral,
            'sommeCoefficients' => $coefficients,
            'eleves' => $elevesId,
            'classe' => $classe,
            'trimestre' => $trimestre,
        ]);
    }

    #[Route('/{classe}/{eleve}/bulletins', name: 'app_notes_bulletin_eleve')]
    public function bulletinEleve(
        Request $request,
        ClassesRepository $classesRepository,
        ExaminationsRepository $examinationsRepository,
        ElevesRepository $elevesRepository,
        NotesRepository $notesRepository,
        ClassesMatieresRepository $classesMatieresRepository,
        EvaluationsRepository $evaluationsRepository,
        EcolesRepository $ecolesRepository,
    ): Response {
        $classeId = $request->get('classe');
        $eleveId = $request->get('eleve');
        $trimestre = $request->get('trimestre');
        $classe = $classesRepository->findOneBy(["id" => $classeId]);
        $eleve = $elevesRepository->findOneBy(["id" => $eleveId]);
        $eleves = $elevesRepository->findBy(["classe" => $classe]);
        $effectif = count($eleves);
        $ecole = $ecolesRepository->findOneBy(['id' => 1]);
        $matieres = [];
        $results1 = [];
        $results2 = [];

        $cMatieres = $classesMatieresRepository->findMatiereByClasse($classe);

        foreach ($cMatieres as $cMatiere) {
            $matieres[$cMatiere->getMatiere()->getId()] = $cMatiere->getMatiere();
        }

        // Calcul des résultats
        $results = $this->calculateBulletins(
            $classe,
            $eleves,  // Passer un tableau avec un seul élève
            $trimestre,
            $classesMatieresRepository,
            $examinationsRepository,
            $notesRepository,
            $evaluationsRepository
        );

        // Classification des moyennes par matière
        $rangParMatiere = [];

        if (isset($results[2])) {
            $this->addFlash($results[2][0], $results[2][1]);
            return $this->redirectToRoute("app_classes_bulletins");
        } else {
            if ($results[1]) {
                $moyennesParMatiere = $results[1];
                foreach ($moyennesParMatiere as $m => $moy) {
                    arsort($moy);
                    $moyennes[$m] = $moy;
                }

                $PlusForteMoyenne = [];
                $PlusFaibleMoyenne = [];

                foreach ($moyennes as $l => $n) {
                    $PlusForteMoyenne[$l] = reset($n);
                    $PlusFaibleMoyenne[$l] = end($n);
                }

                $rangParMatiere = [];
                foreach ($moyennes as $l => $n) {
                    arsort($n);

                    $rang = 1;
                    foreach ($n as $nKey => $nVal) {
                        $rangParMatiere[$l][$nKey] = $rang++;
                    }
                }

                $general = [];
                $rangGeneral = [];
                foreach ($results[0] as $resKey => $resultat) {
                    $general[$resKey] = $resultat["moyenneGenerale"];
                }

                arsort($general);
                $moyenneGForte = reset($general);
                $moyenneGFaible = end($general);

                $moyenneGClasse = round(array_sum($general) / count($eleves), 2);

                $rangG = 1;
                foreach ($general as $elKey => $elMoy) {
                    $rangGeneral[$elKey] = $rangG++;
                }
            } else {
                $this->addFlash('warning', 'il n\'existe pas de bulletin pour cette classe ou ce trimestre');
                return $this->redirectToRoute("app_classes_bulletins");
            }
        }

        // Si c'est le troisième trimestre, on aurait besoin des résultats des trimestres passés
        if ($trimestre == 3) {
            $results1 = $this->calculateBulletins(
                $classe,
                $eleves,
                1,
                $classesMatieresRepository,
                $examinationsRepository,
                $notesRepository,
                $evaluationsRepository
            );
            $results2 = $this->calculateBulletins(
                $classe,
                $eleves,
                2,
                $classesMatieresRepository,
                $examinationsRepository,
                $notesRepository,
                $evaluationsRepository
            );

            if (isset($results1[2])) {

                $this->addFlash($results[2][0], $results[2][1]);
                return $this->redirectToRoute("app_classes_bulletins");
            } else {
                // On ne prend que les moyennes générales de ce trimestre
                $results1 = $results1[0][$eleve->getId()];
            }

            if (isset($results2[2])) {

                $this->addFlash($results[2][0], $results[2][1]);
                return $this->redirectToRoute("app_classes_bulletins");
            } else {
                $results2 = $results2[0][$eleve->getId()];
            }
        }

        return $this->render('/notes/bulletin_eleve.html.twig', [
            'classe' => $classe,
            'effectif' => $effectif,
            'matieres' => $matieres,
            'trimestre' => $trimestre,
            'eleve' => $eleve,
            'rangParMatiere' => $rangParMatiere,
            'plusForteMoyenne' => $PlusForteMoyenne,
            'plusFaibleMoyenne' => $PlusFaibleMoyenne,
            'moyenneGForte' => $moyenneGForte,
            'moyenneGFaible' => $moyenneGFaible,
            'rangGeneral' => $rangGeneral,
            'moyenneGClasse' => $moyenneGClasse,
            'results' => $results[0][$eleve->getId()],
            'ecole' => $ecole,
            'results1' => $results1,
            'results2' => $results2,
            // 't3' => $moyenneTrimestre3,
        ]);
    }

    #[Route('/bulletins/trimestre/{classeId}{trimestre}', name: 'app_notes_bulletins_trimestre')]
    public function showBulletin(
        int $classeId,
        int $trimestre,
        BulletinManager2 $bulletinManager
    ): Response {
        $resultats = $bulletinManager->calculateTrimestre($classeId, $trimestre);

        return $this->render('bulletin/trimestre.html.twig', [
            'resultats' => $resultats,
        ]);
    }
}
