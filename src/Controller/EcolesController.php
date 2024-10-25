<?php

namespace App\Controller;

use App\Entity\Classes;
use App\Entity\ClassesMatieres;
use App\Entity\Ecoles;
use App\Entity\Matieres;
use App\Entity\Tarif;
use App\Entity\TarifBackup;
use App\Form\EcolesType;
use App\Repository\ClassesMatieresRepository;
use App\Repository\ClassesRepository;
use App\Repository\EcolesRepository;
use App\Repository\ElevesRepository;
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
    public function edit(Request $request, Ecoles $ecole, EntityManagerInterface $entityManager, ClassesRepository $classesRepository, ClassesMatieresRepository $classesMatieresRepository, TarifRepository $tarifRepository, ElevesRepository $elevesRepository): Response
    {
        $anneeActuelle = $ecole->getAnneeScolaire()->getId();
        $form = $this->createForm(EcolesType::class, $ecole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On vérifie si on a changé d'année scolaire 
            if ($anneeActuelle != $ecole->getAnneeScolaire()->getId()) {

                // Si c'est le cas on duplique les classes et on assigne la nouvelle année aux classes dupliquées
                $classes = $classesRepository->findAll();
                $j = 1;
                foreach ($classes as $classe) {
                    $verifClasse = $classesRepository->findOneBy([
                        'nom' => $classe->getNom(),
                        'niveau' => $classe->getNiveau(),
                        'classeOrder' => $classe->getClasseOrder(),
                        'annee_scolaire' => $ecole->getAnneeScolaire(),
                    ]);
                    if (!$verifClasse) {
                        $newClasse = new Classes();
                        $newClasse->setNom($classe->getNom());
                        $newClasse->setClasseOrder($classe->getClasseOrder());
                        $newClasse->setNiveau($classe->getNiveau());
                        $newClasse->setAnneeScolaire($ecole->getAnneeScolaire());
                        $entityManager->persist($newClasse);
                    }
                    // Working on Tarif
                    $lastTarif = $tarifRepository->findOneBy(['classe' => $classe]);
                    $verifTarif = $tarifRepository->findOneBy([
                        'classe' => $classe->getNom(),
                        'AnneeScolaire' => $ecole->getAnneeScolaire()
                    ]);
                    if (!$verifTarif) {
                        $tarif = new Tarif($ecole->getAnneeScolaire());
                        $tarif->setPrixAnnuel($lastTarif->getPrixAnnuel());
                        $tarif->setPrixInscription($lastTarif->getPrixInscription());
                        $tarif->setPrixReinscription($lastTarif->getPrixPrixReinscription());
                        $tarif->setClasse($classe);
                        $entityManager->persist($tarif);
                    }

                    // Assignation des matières à la nouvelle classe
                    $cMatieres = $classesMatieresRepository->findMatiereByClasse($classe);
                    if ($cMatieres) {
                        foreach ($cMatieres as $cMatiere) {
                            $newMatiere = new Matieres();
                            $newMatiere->setNom($cMatiere->getMatiere()->getNom());
                            $entityManager->persist($newMatiere);

                            // Working on classeMatiere
                            $verif = $classesMatieresRepository->findOneBy([
                                'classe' => $classe,
                                'matiere' => $newMatiere,
                                'coefficient' => $cMatiere->getCoefficient(),
                                'annee_scolaire' => $ecole->getAnneeScolaire(),
                            ]);
                            if (!$verif) {
                                $newCMatiere = new ClassesMatieres();
                                $newCMatiere->setMatiere($newMatiere);
                                $newCMatiere->setClasse($classe);
                                $newCMatiere->setCoefficient($cMatiere->getCoefficient());
                                $newCMatiere->setAnneeScolaire($ecole->getAnneeScolaire());
                                $entityManager->persist($newCMatiere);
                            }
                        }
                    }

                    // Gestion des élève
                    $eleves = $elevesRepository->findBy(['classe' => $classe]);
                    if ($eleves) {
                    }

                    $j++;
                    if ($j >= 4) {
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
