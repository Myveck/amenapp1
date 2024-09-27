<?php

namespace App\Controller;

use App\Entity\Eleves;
use App\Form\Eleves1Type;
use App\Repository\ClassesRepository;
use App\Repository\ElevesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/eleves')]
final class ElevesController extends AbstractController
{
    #[Route(name: 'app_eleves_index', methods: ['GET'])]
    public function index(ElevesRepository $elevesRepository, ClassesRepository $classesRepository): Response
    {
        return $this->render('eleves/index.html.twig', [
            'eleves' => $elevesRepository->findBy([], ['nom' => 'asc']),
            'classes' => $classesRepository->findBy([], ['classeOrder' => 'asc']),
            'active' => 'all',
        ]);
    }

    #[Route('/note', name: 'app_eleves_note', methods: ['GET'])]
    public function note(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository): Response
    {
        $trie = $request->get('trie');
        return $this->render('eleves/index.html.twig', [
            'eleves' => $elevesRepository->findBy(['classe' => $trie], ['nom' => 'asc']),
            'classes' => $classesRepository->findBy([], ['classeOrder' => 'asc']),
            'active' => 'all',
        ]);
    }

    #[Route('/trier', name: 'app_eleves_trier', methods: ['GET'])]
    public function trier(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository): Response
    {
        $trie = $request->get('trie');
        if ($trie == "all") {
            $eleves = $elevesRepository->findBy([], ['nom' => 'asc']);
        } else {
            $eleves = $elevesRepository->findBy(['classe' => $trie], ['nom' => 'asc']);
        }
        return $this->render('eleves/index.html.twig', [
            'eleves' => $eleves,
            'classes' => $classesRepository->findBy([], ['classeOrder' => 'asc']),
            'active' => $trie,
        ]);
    }

    #[Route('/choice', name: 'app_eleves_choice', methods: ['GET'])]
    public function choice(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository): Response
    {
        $trie = $request->get('trie');
        if (!$trie or $trie == "all") {
            $eleves = $elevesRepository->findBy([], ['nom' => 'asc']);
        } else {
            $eleves = $elevesRepository->findBy(['classe' => $trie], ['nom' => 'asc']);
        }
        return $this->render('eleves/choice.html.twig', [
            'eleves' => $eleves,
            'classes' => $classesRepository->findBy([], ['classeOrder' => 'asc']),
            'active' => $trie,
        ]);
    }

    #[Route('/trier/renew', name: 'app_eleves_trier_renew', methods: ['GET'])]
    public function trierRenew(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository): Response
    {
        $trie = $request->get('trie');
        if ($trie == "all") {
            $eleves = $elevesRepository->findBy([], ['nom' => 'asc']);
        } else {
            $eleves = $elevesRepository->findBy(['classe' => $trie], ['nom' => 'asc']);
        }
        return $this->render('eleves/renew.html.twig', [
            'eleves' => $eleves,
            'classes' => $classesRepository->findBy([], ['classeOrder' => 'asc']),
            'active' => $trie,
        ]);
    }


    #[Route('/new', name: 'app_eleves_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $elefe = new Eleves();
        $form = $this->createForm(Eleves1Type::class, $elefe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($elefe);
            $entityManager->flush();

            return $this->redirectToRoute('app_eleves_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('eleves/new.html.twig', [
            'elefe' => $elefe,
            'form' => $form,
        ]);
    }

    #[Route('/renew', name: 'app_eleves_renew', methods: ['GET', 'POST'])]
    public function renew(ElevesRepository $elevesRepository, ClassesRepository $classesRepository): Response
    {
        return $this->render('eleves/renew.html.twig', [
            'eleves' => $elevesRepository->findAll(),
            'classes' => $classesRepository->findBy([], ['classeOrder' => 'asc']),
            'active' => 'all',
        ]);
    }

    #[Route('/{id}', name: 'app_eleves_show', methods: ['GET'])]
    public function show(Eleves $elefe): Response
    {
        return $this->render('eleves/show.html.twig', [
            'elefe' => $elefe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_eleves_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Eleves $elefe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Eleves1Type::class, $elefe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_eleves_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('eleves/edit.html.twig', [
            'elefe' => $elefe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/promotion', name: 'app_eleves_promotion', methods: ['GET', 'POST'])]
    public function promotion(Request $request, Eleves $elefe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Eleves1Type::class, $elefe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_eleves_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('eleves/promotion.html.twig', [
            'elefe' => $elefe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_eleves_delete', methods: ['POST'])]
    public function delete(Request $request, Eleves $elefe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $elefe->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($elefe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_eleves_index', [], Response::HTTP_SEE_OTHER);
    }
}
