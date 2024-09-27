<?php

namespace App\Controller;

use App\Entity\Enseignants;
use App\Form\EnseignantsType;
use App\Repository\EnseignantsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/enseignants')]
final class EnseignantsController extends AbstractController
{
    #[Route(name: 'app_enseignants_index', methods: ['GET'])]
    public function index(EnseignantsRepository $enseignantsRepository): Response
    {
        return $this->render('enseignants/index.html.twig', [
            'enseignants' => $enseignantsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_enseignants_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $enseignant = new Enseignants();
        $form = $this->createForm(EnseignantsType::class, $enseignant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($enseignant);
            $entityManager->flush();

            return $this->redirectToRoute('app_enseignants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('enseignants/new.html.twig', [
            'enseignant' => $enseignant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_enseignants_show', methods: ['GET'])]
    public function show(Enseignants $enseignant): Response
    {
        return $this->render('enseignants/show.html.twig', [
            'enseignant' => $enseignant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_enseignants_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Enseignants $enseignant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EnseignantsType::class, $enseignant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_enseignants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('enseignants/edit.html.twig', [
            'enseignant' => $enseignant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_enseignants_delete', methods: ['POST'])]
    public function delete(Request $request, Enseignants $enseignant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$enseignant->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($enseignant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_enseignants_index', [], Response::HTTP_SEE_OTHER);
    }
}
