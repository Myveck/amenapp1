<?php

namespace App\Controller;

use App\Entity\Eleves;
use App\Entity\Notes;
use App\Form\NotesType;
use App\Repository\ClassesRepository;
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
            // dd($request);
            $entityManager->persist($note);
            $entityManager->flush();

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
}
