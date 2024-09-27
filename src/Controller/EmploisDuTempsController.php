<?php

namespace App\Controller;

use App\Entity\EmploisDuTemps;
use App\Form\EmploisDuTempsType;
use App\Repository\EmploisDuTempsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/emplois/du/temps')]
final class EmploisDuTempsController extends AbstractController
{
    #[Route(name: 'app_emplois_du_temps_index', methods: ['GET'])]
    public function index(EmploisDuTempsRepository $emploisDuTempsRepository): Response
    {
        return $this->render('emplois_du_temps/index.html.twig', [
            'emplois_du_temps' => $emploisDuTempsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_emplois_du_temps_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $emploisDuTemp = new EmploisDuTemps();
        $form = $this->createForm(EmploisDuTempsType::class, $emploisDuTemp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($emploisDuTemp);
            $entityManager->flush();

            return $this->redirectToRoute('app_emplois_du_temps_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('emplois_du_temps/new.html.twig', [
            'emplois_du_temp' => $emploisDuTemp,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_emplois_du_temps_show', methods: ['GET'])]
    public function show(EmploisDuTemps $emploisDuTemp): Response
    {
        return $this->render('emplois_du_temps/show.html.twig', [
            'emplois_du_temp' => $emploisDuTemp,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_emplois_du_temps_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EmploisDuTemps $emploisDuTemp, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EmploisDuTempsType::class, $emploisDuTemp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_emplois_du_temps_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('emplois_du_temps/edit.html.twig', [
            'emplois_du_temp' => $emploisDuTemp,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_emplois_du_temps_delete', methods: ['POST'])]
    public function delete(Request $request, EmploisDuTemps $emploisDuTemp, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$emploisDuTemp->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($emploisDuTemp);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_emplois_du_temps_index', [], Response::HTTP_SEE_OTHER);
    }
}
