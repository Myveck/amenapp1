<?php

namespace App\Controller;

use App\Entity\Paiements;
use App\Entity\PaiementsBackup;
use App\Form\PaiementsType;
use App\Repository\AnneeScolaireRepository;
use App\Repository\ClassesRepository;
use App\Repository\EcolesRepository;
use App\Repository\ElevesBackupRepository;
use App\Repository\ElevesRepository;
use App\Repository\PaiementsBackupRepository;
use App\Repository\PaiementsRepository;
use App\Repository\TarifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/paiements')]
final class PaiementsController extends AbstractController
{
    #[Route(name: 'app_paiements_index', methods: ['GET'])]
    public function index(PaiementsRepository $paiementsRepository): Response
    {
        return $this->render('paiements/index.html.twig', [
            'paiements' => $paiementsRepository->findAll(),
        ]);
    }

    #[Route('/nouveau', name: 'app_paiements_nouveau', methods: ['GET'])]
    public function nouveau(PaiementsRepository $paiementsRepository): Response
    {
        return $this->redirectToRoute('app_eleves_paiements');
    }

    #[Route('/etat', name: 'app_paiements_etat', methods: ['GET'])]
    public function etat(PaiementsRepository $paiementsRepository, ClassesRepository $classesRepository, ElevesRepository $elevesRepository, TarifRepository $tarifRepository): Response
    {
        $paiements = $paiementsRepository->findAll();
        $classes = $classesRepository->findAll();
        $tarifs = $tarifRepository->findAll();
        $tarifEcole = 0;
        $totalReceived = 0;

        $tranchesParClasse = [];
        $tarifParClasse = [];

        foreach ($tarifs as $tarif) {
            $elevesPC = count($elevesRepository->findBy(['classe' => $tarif->getClasse()]));
            $tarifParClasse[$tarif->getClasse()->getId()] = $tarif->getPrixAnnuel() * $elevesPC;
        }

        foreach ($tarifParClasse as $tarif) {
            $tarifEcole += $tarif;
        }

        foreach ($classes as $classe) {
            $students = $elevesRepository->findBy(['classe' => $classe]);
            $tranchesParClasse[$classe->getId()] = 0;

            if ($students) {
                foreach ($students as $student) {
                    $studentPaiements = $paiementsRepository->findBy(['eleve' => $student]);
                    if ($studentPaiements) {
                        foreach ($studentPaiements as $studentPaiement) {
                            $tranchesParClasse[$classe->getId()] += $studentPaiement->getMontant();
                        }
                    }
                }
            }
        }

        foreach ($tranchesParClasse as $one) {
            $totalReceived += $one;
        }

        // Calcul des tranches reçues par classe
        return $this->render('paiements/etat.html.twig', [
            'classes' => $classes,
            'tranchesParClasse' => $tranchesParClasse,
            'tarifParClasse' => $tarifParClasse,
            'tarifEcole' => $tarifEcole,
            'totalReceived' => $totalReceived,
        ]);
    }

    #[Route('/new/{id}', name: 'app_paiements_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ElevesRepository $elevesRepository, ElevesBackupRepository $elevesBackupRepository, AnneeScolaireRepository $anneeScolaireRepository, EcolesRepository $ecolesRepository): Response
    {
        $paiement = new Paiements();
        $eleve = $elevesRepository->findOneBy(['id' => $request->get('id')]);
        $anneeScolaire = $ecolesRepository->findOneBy(['id' => 1])->getAnneeScolaire();
        $form = $this->createForm(PaiementsType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paiement->setEleveId($eleve);
            $paiement->setAnneeScolaire($anneeScolaire);
            $entityManager->persist($paiement);

            // Gestion du backup
            $paiementBackup = new PaiementsBackup();
            $elevedBacked = $elevesBackupRepository->findOneBy([
                'name' => $eleve->getNom() . ' ' . $eleve->getPrenom(),
                'classe' => $eleve->getClasse()->getNom(),
            ]);

            $paiementBackup->setAnneeScolaire($anneeScolaire);
            $paiementBackup->setEleveBackup($elevedBacked);
            $paiementBackup->setMontant($paiement->getMontant());
            $entityManager->persist($paiementBackup);

            $entityManager->flush();

            $this->addFlash('success', 'Le paiement a été éffectué avec succès');
            return $this->redirectToRoute('app_paiements_nouveau', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paiements/new.html.twig', [
            'paiement' => $paiement,
            'form' => $form,
            'eleve' => $eleve,
        ]);
    }

    #[Route('/{id}', name: 'app_paiements_show', methods: ['GET'])]
    public function show(Paiements $paiement): Response
    {
        return $this->render('paiements/show.html.twig', [
            'paiement' => $paiement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_paiements_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Paiements $paiement, EntityManagerInterface $entityManager, ElevesRepository $elevesRepository): Response
    {
        $form = $this->createForm(PaiementsType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash("success", "Le paiement a été modifié avec succès");
            return $this->redirectToRoute('app_paiements_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paiements/edit.html.twig', [
            'paiement' => $paiement,
            'form' => $form,
            'eleve' => $elevesRepository->findOneBy(['id' => intval($request->get('eleve'))]),
        ]);
    }

    #[Route('/{id}', name: 'app_paiements_delete', methods: ['POST'])]
    public function delete(Request $request, Paiements $paiement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $paiement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($paiement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_paiements_index', [], Response::HTTP_SEE_OTHER);
    }
}
