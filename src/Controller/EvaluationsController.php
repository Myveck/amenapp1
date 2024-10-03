<?php

namespace App\Controller;

use App\Entity\Evaluations;
use App\Form\EvaluationsType;
use App\Repository\EvaluationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/evaluations')]
final class EvaluationsController extends AbstractController
{
    #[Route(name: 'app_evaluations_index', methods: ['GET'])]
    public function index(EvaluationsRepository $evaluationsRepository): Response
    {
        return $this->render('evaluations/index.html.twig', [
            'evaluations' => $evaluationsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_evaluations_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evaluation = new Evaluations();
        $form = $this->createForm(EvaluationsType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($evaluation);
            $entityManager->flush();

            return $this->redirectToRoute('app_evaluations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evaluations/new.html.twig', [
            'evaluation' => $evaluation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evaluations_show', methods: ['GET'])]
    public function show(Evaluations $evaluation): Response
    {
        return $this->render('evaluations/show.html.twig', [
            'evaluation' => $evaluation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evaluations_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evaluations $evaluation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvaluationsType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_evaluations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evaluations/edit.html.twig', [
            'evaluation' => $evaluation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evaluations_delete', methods: ['POST'])]
    public function delete(Request $request, Evaluations $evaluation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evaluation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($evaluation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_evaluations_index', [], Response::HTTP_SEE_OTHER);
    }
}
