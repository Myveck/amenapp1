<?php

namespace App\Controller;

use App\Entity\Eleves;
use App\Entity\Evaluations;
use App\Entity\Matieres;
use App\Entity\Notes;
use App\Form\NotesType;
use App\Repository\ClassesMatieresRepository;
use App\Repository\ClassesRepository;
use App\Repository\EcolesRepository;
use App\Repository\ElevesRepository;
use App\Repository\EvaluationsRepository;
use App\Repository\ExaminationsRepository;
use App\Repository\NotesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            'notes' => $notesRepository->findAll(),
            'classes' => $classesRepository->findAll(),
            'trie' => $trie,
        ]);
    }

    // #[Route('/new', name: 'app_notes_new', methods: ['GET', 'POST'])]
    // public function new(Request $request, EntityManagerInterface $entityManager): Response
    // {
    //     $note = new Notes();
    //     $form = $this->createForm(NotesType::class, $note);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager->persist($note);
    //         $entityManager->flush();

    //         return $this->redirectToRoute('app_notes_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->render('notes/new.html.twig', [
    //         'note' => $note,
    //         'form' => $form,
    //     ]);
    // }

    #[Route('/moyennes', name: 'app_notes_moyennes', methods: ['GET'])]
    public function showMoyennes(Notes $note): Response
    {
        return $this->render('notes/moyennes.html.twig', [
            'note' => $note,
        ]);
    }

    #[Route('/choice', name: 'app_notes_choice', methods: ['GET'])]
    public function showChoice(Request $request, NotesRepository $note, ClassesRepository $classesRepository): Response
    {
        if ($request->get("trie")) {
            $trie = $request->get("trie");
        } else {
            $trie = "all";
        }
        return $this->render('notes/choice.html.twig', [
            'notes' => $note->findAll(),
            'classes' => $classesRepository->findAll(),
            'trie' => $trie,
        ]);
    }

    // #[Route('/moyennes', name: 'app_notes_moyennes', methods: ['GET'])]
    // public function moyenne(): Response
    // {
    //     return $this->render('notes/moyenne_choix.html.twig');
    // }

    #[Route('/create/exam/{examination}', name: 'app_notes_create_exam', methods: ['GET'])]
    public function createExam(Request $request, ElevesRepository $eleveRepository, ExaminationsRepository $examinationsRepository, EntityManagerInterface $entityManager, NotesRepository $notesRepository)
    {
        $notes = $request->get('notes');
        $examination = $examinationsRepository->findOneBy(["id" => $request->get('examination')]);
        foreach ($notes as $id => $noteEleve) {
            $eleve = $eleveRepository->findOneBy(["id" => $id]);

            // Je cherche une note corespondant à l'élève actuel
            $note = $notesRepository->findOneBy([
                "eleve" => $eleve,
                'examinations' => $examination,
            ]);

            if ($note) {
                // Si c'est le cas on update la note actuelle
                $note->setNote($noteEleve);
            } else {

                // Sinon, on créé la note

                $note = new Notes();
                $elevenote = $noteEleve ?: 0.0;
                $note->setNote($elevenote);
                $note->setDateEvaluation($examination->getDateExamination());
                $note->setEleveId($eleve);
                $note->setMatiereId($examination->getMatiere());
                $note->setTrimestre($examination->getTrimestre());
                $note->setEvaluation($examination->getEvaluation());
                $note->setExaminations($examination);
            }
            $entityManager->persist($note);

            $entityManager->flush();
        }

        $this->addFlash("success", "Les notes ont bien été modifiée");
        return $this->redirectToRoute("app_examinations_index", ['classe' => $examination->getClasse()->getId()]);
    }

    #[Route('/moyennes', name: 'app_notes_moyennes', methods: ['GET'])]
    public function showMoyenne(): Response
    {

        return $this->render('notes/choice_moyenne_trimestre.html.twig');
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
            'allow_extra_fields' => $note->getEleveId()->getClasse()
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
            'allow_extra_fields' => $note->getEleveId()->getClasse()
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

    // #[Route('/{classe}/bulletins', name: 'app_notes_bulletins')]
    // public function bulletin(Request $request, ClassesRepository $classesRepository, ExaminationsRepository $examinationsRepository, ElevesRepository $elevesRepository, ClassesMatieresRepository $classesMatieresRepository, MatieresRepository $matieresRepository, EvaluationsRepository $evaluationsRepository): Response
    // {
    //     $d1 = $evaluationsRepository->findOneBy(["id" => 1]);
    //     $d2 = $evaluationsRepository->findOneBy(["id" => 2]);
    //     $mi = $evaluationsRepository->findOneBy(["id" => 3]);
    //     $dh = $evaluationsRepository->findOneBy(["id" => 4]);
    //     // Je commence par prendre la classe ainsi que le trimestre
    //     $classeId = $request->get('classe');
    //     $trimestre = $request->get('trimestre');

    //     $classe = $classesRepository->findOneBy(["id" => $classeId]);

    //     // Je prend les élèves appartennants à cette classe
    //     $eleves = $elevesRepository->findBy(["classe" => $classe]);

    //     // J'initialise le tableau qui contiendra les examinations
    //     $examinations = [];
    //     $moyenneParMatiere = [];
    //     $notesParMatiere = [];

    //     // Je prend toutes les matières de cette classe
    //     $cMatieres = $classesMatieresRepository->findMatiereByClasse($classe);

    //     $matieres = [];
    //     foreach ($cMatieres as $cMatiere) {
    //         $matieres[] = $cMatiere->getMatiere();
    //     }

    //     // Pour chaque matière, je prend les différentes examinations puis calcul la moyenne
    //     foreach ($matieres as $matiere) {
    //         // Ce tableau fera le compte, servant à determiner si telle matière à telle ou telle examination ce qui servirait à calculer la moyenne totale de la matière
    //         $moyenne = [];

    //         $noteD1 = $examinationsRepository->findOneBy([
    //             "matiere" => $matiere,
    //             "evaluation" => $d1,
    //             "trimestre" => 3,
    //         ]);
    //         if ($noteD1) {
    //             $moyenne[] = $noteD1;
    //         }

    //         $noteD2 = $examinationsRepository->findOneBy([
    //             "matiere" => $matiere,
    //             "evaluation" => $d2,
    //             "trimestre" => 3,
    //         ]);
    //         if ($noteD2) {
    //             $moyenne[] = $noteD2;
    //         }

    //         $noteMi = $examinationsRepository->findOneBy([
    //             "matiere" => $matiere,
    //             "evaluation" => $mi,
    //             "trimestre" => 3,
    //         ]);
    //         if ($noteMi) {
    //             $moyenne[] = $noteMi;
    //         }

    //         $noteDh = $examinationsRepository->findOneBy([
    //             "matiere" => $matiere,
    //             "evaluation" => $dh,
    //             "trimestre" => 3,
    //         ]);
    //         if ($noteD1) {
    //             $moyenne[] = $noteDh;
    //         }

    //         // Je vais calculer pour chaque élève la note globale par matière

    //         foreach ($eleves as $eleve) {
    //             // Je commence par initialiser le tableau des matière des élèves à 0
    //             $notesParMatiere[$matiere->getId()][$eleve->getId()] = 0;
    //         }
    //         foreach ($moyenne as $oneNote) {
    //             // Si la note existe, on l'ajoute
    //             if ($oneNote) {
    //                 foreach ($eleves as $eleve) {
    //                     $notesParMatiere[$matiere->getId()][$eleve->getId()] += $oneNote[$eleve->getId()];
    //                 }
    //             }
    //         }
    //         // Je calcule maintenant la moyenne par matière puis j'ajoute le coefficient de chaque matière
    //         foreach ($eleves as $eleve) {
    //             $moyenneParMatiere[$matiere->getId()][$eleve->getId()] = ($notesParMatiere[$matiere->getId()][$eleve->getId()] / count($moyenne)) * $classesMatieresRepository->findCoefficientByMatiere($matiere);
    //         }

    //         // Enfin j'ajoute la matière au tableau contennant les examinations de cette classe
    //         $examinations[$matiere->getId()] = $moyenneParMatiere;
    //     }

    //     // Ici je vais calculer la moyenne générale

    //     return $this->render('/notes/bulletins.html.twig');
    // }

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
        $cMatieres = $classesMatieresRepository->findMatiereByClasse($classe);
        $matieres = array_map(fn($cMatiere) => $cMatiere->getMatiere(), $cMatieres);

        // Initialiser les tableaux pour les résultats
        $results = [];
        $moyenneGenerale = [];
        $sommeDesCoefficients = [];
        $notesParEvaluation = [];
        $moyennesParMatiere = [];

        if (!$eleves) {
            $message = ['warning', 'Cette classe ne contient aucun élève'];
        }

        // Calculer les moyennes et les notes pour chaque élève
        foreach ($eleves as $eleve) {
            $moyenneParMatiere = [];
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
                if ($noteD1) $moyenne[] = $noteD1->getNote();
                if ($noteD2) $moyenne[] = $noteD2->getNote();
                if ($noteMi) $moyenne[] = $noteMi->getNote();
                if ($noteDh) $moyenne[] = $noteDh->getNote();

                // Calcul des notes totales et moyenne par matière
                $notesParMatiere[$matiere->getId()] = array_sum($moyenne);
                $coefficient = $classesMatieresRepository->findCoefficientByMatiere($matiere)[0]["coefficient"];

                $noteCount = count($moyenne);
                if ($noteCount > 0) {
                    $moyenneParMatiere[$matiere->getId()] = round(($notesParMatiere[$matiere->getId()] / $noteCount) * $coefficient, 2);

                    // Ajouter à la moyenne générale et à la somme des coefficients
                    $moyenneGenerale[$eleve->getId()] += $moyenneParMatiere[$matiere->getId()];
                    $sommeDesCoefficients[$eleve->getId()] += $coefficient;
                    $moyennesParMatiere[$matiere->getId()][$eleve->getId()] = $moyenneParMatiere[$matiere->getId()];
                }
            }

            // Calcul de la moyenne générale pour l'élève
            if ($sommeDesCoefficients[$eleve->getId()] > 0) {
                $moyenneGenerale[$eleve->getId()] = round($moyenneGenerale[$eleve->getId()] / $sommeDesCoefficients[$eleve->getId()], 2);
            }

            // Stocker les résultats pour cet élève
            $results[$eleve->getId()] = [
                'moyenneParMatiere' => $moyenneParMatiere,
                'moyenneGenerale' => $moyenneGenerale[$eleve->getId()],
                'notesParEvaluation' => $notesParEvaluation[$eleve->getId()],
            ];
        }

        if (isset($message)) {
            return [$results, $moyennesParMatiere, $message];
        }

        return [$results, $moyennesParMatiere];
    }

    #[Route('/{classe}/bulletins', name: 'app_notes_bulletins')]
    public function bulletin(
        Request $request,
        ClassesRepository $classesRepository,
        ExaminationsRepository $examinationsRepository,
        ElevesRepository $elevesRepository,
        ClassesMatieresRepository $classesMatieresRepository,
        EvaluationsRepository $evaluationsRepository,
        NotesRepository $notesRepository,
        EcolesRepository $ecolesRepository,
    ): Response {
        $classeId = $request->get('classe');
        $trimestre = $request->get('trimestre');
        $classe = $classesRepository->findOneBy(["id" => $classeId]);
        $eleves = $elevesRepository->findBy(["classe" => $classe]);
        $effectif = count($eleves);
        $ecole = $ecolesRepository->findOneBy(['id' => 1]);
        $matieres = [];
        $matiereCoef = [];
        $results1 = [];
        $results2 = [];

        $cMatieres = $classesMatieresRepository->findMatiereByClasse($classe);

        foreach ($cMatieres as $cMatiere) {
            $matieres[$cMatiere->getMatiere()->getId()] = $cMatiere->getMatiere();
            $matiereCoef[$cMatiere->getMatiere()->getId()] = $cMatiere->getCoefficient();
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

        // Classification des élèves avec Id comme clé dans un tableau afin de mieux les utiliser dans le twig
        $students = [];
        foreach ($eleves as $un) {
            $students[$un->getId()] = $un;
        }

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
                $this->addFlash('danger', 'Il n\'existe pas de bulletin pour cette classe ou ce trimestre');
                return $this->redirectToRoute("app_classes_bulletins");
            }
        }

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
        $eleves = $elevesRepository->findBy(["classe" => $classe]);
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

    #[Route('/{classe}/verif/bulletins', name: 'app_notes_verif_bulletins', methods: ['GET', 'POST'])]
    public function verifBulletin(
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
        $eleves = $elevesRepository->findBy(["classe" => $classe]);
        $ecole = $ecolesRepository->findOneBy(['id' => 1]);
        $elevesId = [];
        $matieres = [];
        $coefficients = 0;

        $cMatieres = $classesMatieresRepository->findMatiereByClasse($classe);

        if (!$cMatieres) {
            $this->addFlash('warning', 'Il n\existe pas de matière pour cette classe ');
            return $this->redirectToRoute("app_classes_bulletins");
        } else {
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

            $notesEvaluations = [];

            foreach ($results[0] as $unEleve => $result) {
                $notesEvaluations[$unEleve]['d1'] = 0;
                $notesEvaluations[$unEleve]['d2'] = 0;
                $notesEvaluations[$unEleve]['mi'] = 0;
                $notesEvaluations[$unEleve]['dh'] = 0;
                foreach ($result["notesParEvaluation"] as $evals) {
                    foreach ($evals as $keyV => $eval) {
                        $notesEvaluations[$unEleve][$keyV] += $eval;
                    }
                }
            }
        }

        return $this->render('notes/verif_bulletins.html.twig', [
            'results' => $results[0],
            'ecole' => $ecole,
            'rangGeneral' => $rangGeneral,
            'sommeCoefficients' => $coefficients,
            'eleves' => $elevesId,
            'classe' => $classe,
            'trimestre' => $trimestre,
            'notesEvaluations' => $notesEvaluations,
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

    #[Route('/{trimestre}/{todo}/moyennes', name: 'app_notes_moyennes_trimestre', methods: ['GET'])]
    public function calculerMoyennesTrimestre(
        Request $request,
        ElevesRepository $eleveRepository,
        NotesRepository $noteRepository,
        ClassesMatieresRepository $classeMatiereRepository,
        EvaluationsRepository $evaluationsRepository,
        ClassesRepository $classesRepository,
    ): Response {

        // Si un élève n'a pas composé une matière, on doit lui donner une note afin que les résultats soient conformes

        // Une fonction pour calculer la moyenne d'une matière donnée
        function calculMoyenneMatiere(
            NotesRepository $noteRepository,
            Eleves $eleve,
            Matieres $matiere
        ) {
            $moyenneNote = 0;
            $notes = $noteRepository->findBy([
                "eleve" => $eleve,
                "matiere" => $matiere,
            ]);
            if (count($notes) === 0) {
                return 0;
            }
            foreach ($notes as $note) {
                $moyenneNote += $note->getNote();
            }

            return round($moyenneNote / count($notes), 2);
        }

        // Une fonction pour calculer les moyennes par devoir
        function calculMoyenneParDevoir(
            NotesRepository $noteRepository,
            Eleves $eleve,
            Evaluations $evaluations
        ) {
            $moyenneParDevoir = 0;
            $notes = $noteRepository->findBy([
                "eleve" => $eleve,
                "evaluation" => $evaluations,
            ]);

            if (!$notes) {
                return 0;
            }

            foreach ($notes as $note) {
                $moyenneParDevoir += $note->getNote();
            }
            return round($moyenneParDevoir / count($notes), 2);
        }

        // Une fonction pour récupérer toutes les notes d'une composition donnée
        function takeNoteDevoir(NotesRepository $noteRepository, Eleves $eleve, Evaluations $evaluations)
        {
            $noteDevoir = [];
            $all = [];
            $notes = $noteRepository->findBy([
                "eleve" => $eleve,
                "evaluation" => $evaluations,
            ]);

            if (!$notes) {
                return 0;
            }

            foreach ($notes as $note) {
                $noteDevoir[$note->getMatiereId()->getNom()] = $note->getNote();
            }

            $all[$evaluations->getNom()] = $noteDevoir;

            return $all;
        }

        // Je récupère tous les élèves ainsi que le trimestre pour retirer les évaluations d'un certain trimestre
        $eleves = $eleveRepository->findAll();
        $trimestre = intval($request->get("trimestre"));
        $todo = $request->get("todo"); //  Me permet de savoir si je doit renvoyer vers la liste de vérifications des résultats ou de retirement.
        $trie = $request->get("trie");
        $resultats = [];

        $active = $classesRepository->findOneBy(["id" => $trie]);

        // Ici je calcul les moyennes de chaque élève pour chacun des élèves
        foreach ($eleves as $eleve) {
            // J'initialise évaluations 
            $evaluations  = [];
            $toutesEvaluations = [];
            // Je commence par prendre la classe ainsi que les notes de cet élève particulier
            $classe = $eleve->getClasse();
            $notes = $noteRepository->findByEleveTrimestre($eleve, $trimestre);

            // S'il existe des notes je procède à la suite, qui consite à calculer les moyennes
            if ($notes) {
                // Je récupère chaque type d'évaluation.
                // Je compte les utiliser plus tard, pour récupérer les notes de chacune d'elles attachés à une matière particulière
                $devoir1 = $evaluationsRepository->findOneBy(["nom" => "Devoir 1"]);
                $devoir2 = $evaluationsRepository->findOneBy(["nom" => "Devoir 2"]);
                $mi = $evaluationsRepository->findOneBy(["nom" => "MI"]);

                // Je calcule la moyenne général de l'élève
                foreach ($notes as $note) {

                    // Je commence par trouver la matière par rapport à la classe
                    $matiere = $classeMatiereRepository->findBy(["classe" => $classe]);
                    $moyenneParMatiere = [];
                    $sommeCoefficients = 0;
                    $moyenneMatieres = 0;

                    foreach ($matiere as $one) {
                        // Je calcule la moyenne général de chaque matière et les place dans un tableau
                        $moyenneParMatiere[$one->getMatiere()->getNom()] = calculMoyenneMatiere($noteRepository, $eleve, $one->getMatiere());
                        $sommeCoefficients += $one->getCoefficient();

                        // Je renvois chaque matière ainsi que ses trois notes et son coefficient
                        $evaluations[$one->getMatiere()->getNom()] = [
                            $noteRepository->findByEleveEvaluationMatiere(
                                $eleve,
                                $mi->getId(),
                                $one->getMatiere(),
                            ),
                            $noteRepository->findByEleveEvaluationMatiere(
                                $eleve,
                                $devoir1->getId(),
                                $one->getMatiere(),
                            ),
                            $noteRepository->findByEleveEvaluationMatiere(
                                $eleve,
                                $devoir2->getId(),
                                $one->getMatiere(),
                            ),
                            $one->getCoefficient(),
                            $moyenneParMatiere[$one->getMatiere()->getNom()],
                        ];
                    }
                }

                // Je calcule la moyenne général de toutes les matières puis je la divise par la somme des coefficients pour avoir la moyenne de l'élève
                foreach ($moyenneParMatiere as $oneMPM) {
                    $moyenneMatieres += $oneMPM;
                }
                $moyenneGenerale = round($moyenneMatieres / $sommeCoefficients, 2);


                $first = calculMoyenneParDevoir($noteRepository, $eleve, $devoir1);
                $second = calculMoyenneParDevoir($noteRepository, $eleve, $devoir2);
                $third = calculMoyenneParDevoir($noteRepository, $eleve, $mi);

                // J'ajoute le résultat de l'élève dans le tableau des résultats
                $resultats[$eleve->getId()] = [
                    'eleve' => $eleve,
                    'moyenneGenerale' => $moyenneGenerale,
                    'moyenneParMatiere' => $moyenneParMatiere,
                    'moyenneDesMatieres' => $moyenneMatieres,
                    'sommeCoefficients' => $sommeCoefficients,
                    'moyenneDevoir1' => $first,
                    'moyenneDevoir2' => $second,
                    'moyenneMi' => $third,
                    'notesEvaluations' => $toutesEvaluations,
                    'notes' => $evaluations,
                ];
            }
        }

        $moyennesGerales = [];
        $rangMatiere = [];
        $forte = [];
        $faible = [];

        foreach ($resultats as $key => $resultat) {
            $moyennesGerales[$key] = $resultat["moyenneGenerale"];

            // Je place chaque matière ainsi que toutes les moyennes selon la classe
            foreach ($resultat["notes"] as $uneCle => $oneNote) {
                $rangMatiere[$resultat["eleve"]->getClasse()->getNom()][$uneCle][$resultat["eleve"]->getId()] = $oneNote[4];
            }
        }

        foreach ($rangMatiere as $kr => $rm) {
            foreach ($rm as $r => $m) {
                arsort($m);

                // Je met toutes les moyennes d'une matière d'une classe dans un tableau avec comme clé les id des élèves
                $rangMatiere[$kr][$r] = $m;

                // Je prend la plus forte et faible moyenne de chaque matière de chaque classe
                $forte[$kr][$r] = reset($m);
                $faible[$kr][$r] = end($m);
            }
        }

        // Je trie les moyennes générales par ordre croissant
        arsort($moyennesGerales);

        // Je classe les rangs de la classe
        $i = 1;
        $rangs = [];
        foreach ($moyennesGerales as $une => $moyenne) {
            // Je place le rang à la cle "une" qui est ici l'identifiant de l'élève
            $rangs[$une] = $i;
            $i += 1;
        }

        // Je prend le rend des élèves par matière
        $matiereRang = [];
        foreach ($rangMatiere as $mr => $rangM) {
            foreach ($rangM as $rangMCle => $rangMOne) {
                $i = 1;
                foreach ($rangMOne as $oneRmC => $rmc) {
                    $matiereRang[$mr][$rangMCle][$oneRmC] = $i;
                    $i += 1;
                }
            }
        }

        // foreach ($resultats as $resultat) {
        //     foreach ($rangs as $cle => $rang) {
        //         if ($cle == $resultat["eleve"]->getId()) {
        //             $resultat["notes"][] = $rang;
        //             dd($resultats);
        //         }
        //     }
        // }

        // Je prend les moyennes de toutes les classes
        $moyennesClasse = [];
        foreach ($resultats as $resultat) {
            $moyennesClasse[$resultat["eleve"]->getClasse()->getNom()][] = $resultat["moyenneGenerale"];
            arsort($moyennesClasse[$resultat["eleve"]->getClasse()->getNom()]);
        }

        // Trouver la moyenne forte, faible et générale d'une classe
        $mcFo = [];
        $mcFa = [];
        $mcG = [];
        foreach ($moyennesClasse as $mycC => $mycOne) {
            foreach ($mycOne as $oneMyc) {
                if (isset($mcG[$mycC])) {
                    $mcG[$mycC] = $mcG[$mycC] + $oneMyc;
                } else {
                    $mcG[$mycC] = $oneMyc;
                }
            }
            $mcFo[$mycC] = reset($mycOne);
            $mcFa[$mycC] = end($mycOne);

            $mcG[$mycC] = $mcG[$mycC] / count($mycOne);
        }


        if ($todo != "bulletin") {
            return $this->render('notes/show_moyennes.html.twig', [
                'moyennes' => $resultats,
                'todo' => $todo,
            ]);
        }

        return $this->render('notes/show_bulletin_trimestre.html.twig', [
            'moyennes' => $resultats,
            'todo' => $todo,
            'classes' => $classesRepository->findBy([], ["classeOrder" => "asc"]),
            'active' => $trie,
            'rangs' => $rangs,
            'forteMoyenne' => $forte,
            'faibleMoyenne' => $faible,
            'rangMatiere' => $matiereRang,
            'ForteMClasse' => $mcFo,
            'FaibleMClasse' => $mcFa,
            'moyenneClasse' => $mcG,
        ]);
    }
}
