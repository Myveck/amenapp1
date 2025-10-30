<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Form\Inscription1Type;
use App\Form\InscriptionType;
use App\Repository\AnneeScolaireRepository;
use App\Repository\ElevesRepository;
use App\Repository\InscriptionRepository;
use App\Service\InscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inscription')]
final class InscriptionController extends AbstractController
{
    #[Route(name: 'app_inscription_index', methods: ['GET'])]
    public function index(InscriptionRepository $inscriptionRepository): Response
    {
        return $this->render('inscription/index.html.twig', [
            'inscriptions' => $inscriptionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_inscription_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $inscription = new Inscription();
        $form = $this->createForm(Inscription1Type::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($inscription);
            $entityManager->flush();

            return $this->redirectToRoute('app_inscription_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('inscription/new.html.twig', [
            'inscription' => $inscription,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/reinscription', name: 'app_inscription_reinscription', methods: ['GET', 'POST'])]
    public function reinscription(int $id, InscriptionRepository $inscriptionRepository, ElevesRepository $elevesRepo, AnneeScolaireRepository $anneeRepo, EntityManagerInterface $em, Request $request, InscriptionManager $inscriptionManager): Response
    {
        $eleve = $elevesRepo->find($id);
        $oldClasse = $inscriptionRepository->findOneBy(["eleve" => $eleve])->getClase();
        if (!$eleve) {
            throw $this->createNotFoundException("Élève non trouvé");
        }

        $annee = $anneeRepo->findOneBy(['actif' => true]);
        if (!$annee) {
            $this->addFlash('danger', 'Aucune année scolaire active trouvée.');
            return $this->redirectToRoute('app_eleves_index');
        }


           // Crée une nouvelle inscription pour la nouvelle année
        $inscription = new Inscription();
        $inscription->setEleve($eleve);
        $inscription->setAnneeScolaire($annee);

        $form = $this->createForm(InscriptionType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $inscriptionManager->reinscrire($eleve, $inscription, $em, $inscriptionRepository);
            $this->addFlash('success', 'Réinscription effectuée avec succès !');
            return $this->redirectToRoute('app_eleves_index', ['trie' => $oldClasse->getId()]);
        }


        return $this->render('eleves/reinscription.html.twig', [
            'eleve' => $eleve,
            'form' => $form->createView(),
            'annee_actuelle' => $annee->getAnnee(),
        ]);
    }

    #[Route('/{id}', name: 'app_inscription_show', methods: ['GET'])]
    public function show(Inscription $inscription): Response
    {
        return $this->render('inscription/show.html.twig', [
            'inscription' => $inscription,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_inscription_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Inscription $inscription, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Inscription1Type::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_inscription_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('inscription/edit.html.twig', [
            'inscription' => $inscription,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_inscription_delete', methods: ['POST'])]
    public function delete(Request $request, Inscription $inscription, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$inscription->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($inscription);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_inscription_index', [], Response::HTTP_SEE_OTHER);
    }
}
