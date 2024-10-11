<?php

namespace App\Controller;

use App\Entity\Classes;
use App\Entity\ClassesMatieres;
use App\Entity\Matieres;
use App\Entity\Tarif;
use App\Form\MatieresType;
use App\Repository\AnneeScolaireRepository;
use App\Repository\ClassesMatieresRepository;
use App\Repository\ClassesRepository;
use App\Repository\MatieresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/matieres')]
final class MatieresController extends AbstractController
{
    #[Route(name: 'app_matieres_index', methods: ['GET'])]
    public function index(Request $request, MatieresRepository $matieresRepository, ClassesMatieresRepository $classeMatiere, ClassesRepository $classesRepository): Response
    {
        $trie = $request->get("trie", "all");
        $matiereCoef = [];

        if ($trie === "all") {
            // Récupérer toutes les matières triées par nom
            $matieres = $matieresRepository->findBy([], ['nom' => 'asc']);

            foreach ($matieres as $matiere) {
                $matiereCoef[$matiere->getId()] = $classeMatiere->findOneBy(['matiere' => $matiere]);
            }
        } else {
            // Récupérer la classe spécifiée par l'ID
            $classe = $classesRepository->find($trie);

            if ($classe) {
                // Récupérer uniquement les matières associées à cette classe
                $matieres = $classeMatiere->findMatiereByClasse($classe);
            } else {
                // Si la classe n'existe pas, retourner un tableau vide
                $matieres = [];
            }
            dump($matieres);
            // Récupération des coefficients pour chaque matière
            foreach ($matieres as $matiere) {
                $matiereCoef[$matiere->getId()] = $classeMatiere->findOneBy(['matiere' => $matiere, 'classe' => $classe]);
                dump($matiereCoef);
            }
        }




        return $this->render('matieres/index.html.twig', [
            'matieres' => $matiereCoef,
            'classeMatieres' => $classeMatiere->findBy([], ['classe' => 'asc']),
            'niveaux' => ['primaire', 'college', 'lycee'],
            'active' => $trie,
            'classes' => $classesRepository->findBy([], ["classeOrder" => "asc"])
        ]);
    }

    #[Route('/creation', name: 'app_matieres_creation', methods: ['GET', 'POST'])]
    public function creation(
        Request $request,
        EntityManagerInterface $entityManager,
        ClassesRepository $classesRepository,
        AnneeScolaireRepository $anneeScolaireRepository,
        MatieresRepository $matieresRepository,
        ClassesMatieresRepository $classesMatieresRepository
    ): Response {
        $classe = $classesRepository->findOneBy(['id' => 15]);
        $matieres = $matieresRepository->findAll();
        $anneeScolaire = $anneeScolaireRepository->findOneBy(['id' => 1]);

        // To avoid cloning the same Matiere multiple times
        $clonedMatieres = [];

        foreach ($matieres as $matiere) {
            if ($matiere->getId() != 19) {
                // Vérifier si cette matière existe déjà pour cette classe
                $existingClasseMatiere = $classesMatieresRepository->findOneBy([
                    'classe' => $classe,
                    'matiere' => $matiere
                ]);

                if (!$existingClasseMatiere) {
                    // Check if we've already cloned this Matiere in this request
                    if (!array_key_exists($matiere->getId(), $clonedMatieres)) {
                        // Cloning the Matiere
                        $nouvelle = new Matieres();
                        $nouvelle->setNom($matiere->getNom());
                        $entityManager->persist($nouvelle);

                        // Add the cloned Matiere to the tracking array
                        $clonedMatieres[$matiere->getId()] = $nouvelle;
                    } else {
                        // Reuse the already cloned Matiere
                        $nouvelle = $clonedMatieres[$matiere->getId()];
                    }

                    // Create the ClasseMatiere association
                    $classeMatiere = new ClassesMatieres();
                    $classeMatiere->setMatiere($nouvelle);
                    $classeMatiere->setClasse($classe);
                    $classeMatiere->setAnneeScolaire($anneeScolaire);

                    // Set coefficient
                    $coef = ($classe->getId() < 16) ? 1 : 2;
                    $classeMatiere->setCoefficient($coef);

                    $entityManager->persist($classeMatiere);
                    $entityManager->flush();
                }
            }
        }

        return $this->redirectToRoute('app_matieres_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/new', name: 'app_matieres_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ClassesRepository $classesRepository,
        AnneeScolaireRepository $anneeScolaireRepository
    ): Response {
        $matiere = new Matieres();
        $tarif = new Tarif();
        $classeMatiere = new ClassesMatieres();

        $form = $this->createForm(MatieresType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($matiere);

            $anneeScolaire = $anneeScolaireRepository->findOneBy(['id' => $request->get("annee_scolaire")]);

            // Working on classeMatiere
            $classeMatiere->setMatiere($matiere);
            $classeMatiere->setClasse($classesRepository->findOneBy(['id' => $request->get("classe")]));
            $classeMatiere->setCoefficient($request->get("coefficient"));
            $classeMatiere->setAnneeScolaire($anneeScolaire);

            $entityManager->persist($classeMatiere);
            $entityManager->flush();


            return $this->redirectToRoute('app_matieres_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('matieres/new.html.twig', [
            'matiere' => $matiere,
            'classes' => $classesRepository->findBy([], ['classeOrder' => 'asc']),
            'form' => $form,
            'selectedClasse' => 1,
            'selectedCoef' => 1,
        ]);
    }

    #[Route('/{id}', name: 'app_matieres_show', methods: ['GET'])]
    public function show(Matieres $matiere): Response
    {
        return $this->render('matieres/show.html.twig', [
            'matiere' => $matiere,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_matieres_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Matieres $matiere, EntityManagerInterface $entityManager, ClassesRepository $classesRepository, ClassesMatieresRepository $classesMatieresRepository): Response
    {

        $classeMatiere = $classesMatieresRepository->findOneBy(['matiere' => $matiere]);

        $form = $this->createForm(MatieresType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Working on classeMatiere
            $classeMatiere->setClasse($classesRepository->findOneBy(['id' => $request->get("classe")]));
            $classeMatiere->setCoefficient($request->get("coefficient"));
            $classeMatiere->setMatiere($matiere);

            $entityManager->persist($classeMatiere);
            $entityManager->flush();

            return $this->redirectToRoute('app_matieres_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('matieres/edit.html.twig', [
            'matiere' => $matiere,
            'classes' => $classesRepository->findBy([], ['classeOrder' => 'asc']),
            'form' => $form,
            'selectedClasse' => $classeMatiere->getClasse()->getId(),
            'selectedCoef' => $classeMatiere->getCoefficient(),
        ]);
    }

    #[Route('/{id}', name: 'app_matieres_delete', methods: ['POST'])]
    public function delete(Request $request, Matieres $matiere, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $matiere->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($matiere);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_matieres_index', [], Response::HTTP_SEE_OTHER);
    }
}
