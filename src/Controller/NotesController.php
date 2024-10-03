<?php

namespace App\Controller;

use App\Entity\Eleves;
use App\Entity\Evaluations;
use App\Entity\Matieres;
use App\Entity\Notes;
use App\Form\NotesType;
use App\Repository\ClassesMatieresRepository;
use App\Repository\ClassesRepository;
use App\Repository\ElevesRepository;
use App\Repository\EvaluationsRepository;
use App\Repository\NotesRepository;
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
