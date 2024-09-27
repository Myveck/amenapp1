<?php

namespace App\Controller;

use App\Entity\Classes;
use App\Entity\ClassesMatieres;
use App\Entity\Matieres;
use App\Form\MatieresType;
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
    public function index(Request $request, MatieresRepository $matieresRepository, ClassesMatieresRepository $classeMatiere): Response
    {
        $trie = $request->get("trie");
        if (!$trie) {
            $trie = "all";
            $matieres = $matieresRepository->findBy([], ['nom' => 'asc']);
        } else {
            $matieres = $classeMatiere->findMatiereByClasseLevel($trie);
        }
        dd($matieres);
        return $this->render('matieres/index.html.twig', [
            'matieres' => $matieres,
            'classeMatieres' => $classeMatiere->findAll(),
            'niveaux' => ['primaire', 'college', 'lycee'],
            'active' => $trie,
        ]);
    }

    #[Route('/new', name: 'app_matieres_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ClassesRepository $classesRepository): Response
    {
        $matiere = new Matieres();
        $classeMatiere = new ClassesMatieres();

        $form = $this->createForm(MatieresType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($matiere);

            // Working on classeMatiere
            $classeMatiere->setMatiere($matiere);
            $classeMatiere->setClasse($classesRepository->findOneBy(['id' => $request->get("classe")]));
            $classeMatiere->setCoefficient($request->get("coefficient"));

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
