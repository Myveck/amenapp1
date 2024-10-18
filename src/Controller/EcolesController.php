<?php

namespace App\Controller;

use App\Entity\Ecoles;
use App\Form\EcolesType;
use App\Repository\EcolesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ecoles')]
final class EcolesController extends AbstractController
{
    #[Route(name: 'app_ecoles_index', methods: ['GET'])]
    public function index(EcolesRepository $ecolesRepository): Response
    {
        return $this->render('ecoles/index.html.twig', [
            'ecoles' => $ecolesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_ecoles_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ecole = new Ecoles();
        $form = $this->createForm(EcolesType::class, $ecole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ecole);
            $entityManager->flush();

            return $this->redirectToRoute('app_ecoles_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ecoles/new.html.twig', [
            'ecole' => $ecole,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ecoles_show', methods: ['GET'])]
    public function show(Ecoles $ecole): Response
    {
        return $this->render('ecoles/show.html.twig', [
            'ecole' => $ecole,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ecoles_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ecoles $ecole, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EcolesType::class, $ecole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash("success", "Modifications effectuées avec succès");
            return $this->redirectToRoute('app_ecoles_edit', ["id" => 1], Response::HTTP_SEE_OTHER);
        }

        if ($request->get("ed")) {
            $this->addFlash("success", "Modifier l'année actuelle en bas");
        }
        return $this->render('ecoles/edit.html.twig', [
            'ecole' => $ecole,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ecoles_delete', methods: ['POST'])]
    public function delete(Request $request, Ecoles $ecole, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ecole->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ecole);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ecoles_index', [], Response::HTTP_SEE_OTHER);
    }
}
