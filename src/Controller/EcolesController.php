<?php

namespace App\Controller;

use App\Entity\Ecoles;
use App\Entity\TarifBackup;
use App\Form\EcolesType;
use App\Repository\ClassesMatieresRepository;
use App\Repository\ClassesRepository;
use App\Repository\EcolesRepository;
use App\Repository\PaiementsRepository;
use App\Repository\TarifBackupRepository;
use App\Repository\TarifRepository;
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
    public function edit(Request $request, Ecoles $ecole, EntityManagerInterface $entityManager, ClassesRepository $classesRepository, ClassesMatieresRepository $classesMatieresRepository, TarifRepository $tarifRepository, PaiementsRepository $paiementsRepository, TarifBackupRepository $tarifBackupRepository): Response
    {
        $anneeActuelle = $ecole->getAnneeScolaire()->getId();
        $form = $this->createForm(EcolesType::class, $ecole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On vérifie si on a changé d'année scolaire 
            if ($anneeActuelle != $ecole->getAnneeScolaire()->getId()) {
                $classes = $classesRepository->findAll();
                $cMatieres = $classesMatieresRepository->findAll();
                $tarifs = $tarifRepository->findAll();
                $paiements = $paiementsRepository->findAll();

                // Si c'est le cas on assigne la nouvelle année scolaire à toutes les entités qui sont liées à l'année scolaire
                $j = 1;
                foreach ($classes as $classe) {
                    $classe->setAnneeScolaire($ecole->getAnneeScolaire());
                    $entityManager->persist($classe);
                    $j++;
                    if ($j > 20) {
                        $entityManager->flush();
                        $entityManager->clear();
                        $j = 0;
                    }
                }
                foreach ($cMatieres as $cMatiere) {
                    $j++;
                    $cMatiere->setAnneeScolaire($ecole->getAnneeScolaire());
                    $entityManager->persist($cMatiere);
                    if ($j > 20) {
                        $entityManager->flush();
                        $entityManager->clear();
                        $j = 0;
                    }
                }
                foreach ($tarifs as $tarif) {
                    $j++;
                    $tarif->setAnneeScolaire($ecole->getAnneeScolaire());
                    $entityManager->persist($tarif);

                    // Ajout des tarifs de cette année dans la table backupTarif
                    $findTB = $tarifBackupRepository->findOneBy([
                        'annee_scolaire' => $ecole->getAnneeScolaire(),
                        'classe' => $tarif->getClasse(),
                    ]);
                    if (!$findTB) {
                        $tarifB = new TarifBackup();
                        $tarifB->setAnneeScolaire($ecole->getAnneeScolaire());
                        $tarifB->setClasse($tarif->getClasse()->getNom());
                        $tarifB->setPrixAnnuel($tarif->getPrixAnnuel());
                        $tarifB->setPrixInscription($tarif->getPrixInscription());
                        $tarifB->setPrixReinscription($tarif->getPrixReinscription());
                        $entityManager->persist($tarifB);
                    }

                    if ($j > 10) {
                        $entityManager->flush();
                        $entityManager->clear();
                        $j = 0;
                    }
                }
            }

            $entityManager->flush();
            $this->addFlash("success", "Modifications effectuées avec succès");

            return $this->redirectToRoute('app_ecoles_edit', ["id" => 1], Response::HTTP_SEE_OTHER);
        }

        if ($request->get("ed")) {
            $this->addFlash("warning", "Modifier l'année scolaire actuelle en bas");
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
