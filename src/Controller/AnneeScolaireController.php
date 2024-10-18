<?php

namespace App\Controller;

use App\Entity\AnneeScolaire;
use App\Form\AnneeScolaireType;
use App\Repository\AnneeScolaireRepository;
use App\Repository\EcolesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/annee/scolaire')]
final class AnneeScolaireController extends AbstractController
{
    #[Route(name: 'app_annee_scolaire_index', methods: ['GET'])]
    public function index(AnneeScolaireRepository $anneeScolaireRepository): Response
    {
        return $this->render('annee_scolaire/index.html.twig', [
            'annee_scolaires' => $anneeScolaireRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_annee_scolaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EcolesRepository $ecolesRepository, AnneeScolaireRepository $anneeScolaireRepository): Response
    {
        $anneeScolaire = new AnneeScolaire();
        $form = $this->createForm(AnneeScolaireType::class, $anneeScolaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($anneeScolaire);

            $find = $anneeScolaireRepository->findOneBy([
                'annee' => $anneeScolaire->getAnnee()
            ]);
            if (!$find) {
                $ecole = $ecolesRepository->findOneBy(['id' => 1]);
                $ecole->setAnneeScolaire($anneeScolaire);
                $entityManager->persist($ecole);

                $entityManager->flush();

                $this->addFlash("success", "L'année scolaire a été ajoutée avec succès");
                $this->addFlash("success", "L'année scolaire actuelle est : " . $anneeScolaire->getAnnee());

                return $this->redirectToRoute('app_ecoles_edit', ['id' => 1], Response::HTTP_SEE_OTHER);
            } else {

                $this->addFlash("warning", "L'année scolaire existe déjà");
                $this->addFlash("warning", "Veuillez choisir l'année en bas de la page et enregistre les modifications");

                return $this->redirectToRoute('app_ecoles_edit', ['id' => 1], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('annee_scolaire/new.html.twig', [
            'annee_scolaire' => $anneeScolaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_annee_scolaire_show', methods: ['GET'])]
    public function show(AnneeScolaire $anneeScolaire): Response
    {
        return $this->render('annee_scolaire/show.html.twig', [
            'annee_scolaire' => $anneeScolaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_annee_scolaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AnneeScolaire $anneeScolaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnneeScolaireType::class, $anneeScolaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_annee_scolaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('annee_scolaire/edit.html.twig', [
            'annee_scolaire' => $anneeScolaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_annee_scolaire_delete', methods: ['POST'])]
    public function delete(Request $request, AnneeScolaire $anneeScolaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $anneeScolaire->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($anneeScolaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_annee_scolaire_index', [], Response::HTTP_SEE_OTHER);
    }
}
