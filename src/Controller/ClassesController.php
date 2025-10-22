<?php

namespace App\Controller;

use App\Entity\Classes;
use App\Entity\ClassesBackup;
use App\Entity\Tarif;
use App\Entity\TarifBackup;
use App\Form\ClassesType;
use App\Repository\ClassesBackupRepository;
use App\Repository\ClassesRepository;
use App\Repository\EcolesRepository;
use App\Repository\ElevesRepository;
use App\Repository\TarifBackupRepository;
use App\Repository\TarifRepository;
use App\Repository\AnneeScolaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/classes')]
final class ClassesController extends AbstractController
{
    #[Route(name: 'app_classes_index', methods: ['GET'])]
    public function index(Request $request, ClassesRepository $classesRepository, TarifRepository $tarifRepository, EcolesRepository $ecolesRepository, AnneeScolaireRepository $anneeSRepositroy): Response
    {
        $anneeScolaire = $anneeSRepositroy->findOneBy(['actif' => 1]);

        $trie = $request->get('trie');
        if (!$trie or $trie == "all") {
            $trie = "all";
            $classes = $classesRepository->findBy(['annee_scolaire' => $anneeScolaire], ['classeOrder' => 'asc']);
        } else {

            $classes = $classesRepository->findBy(
                [
                    "niveau" => $trie,
                    'annee_scolaire' => $anneeScolaire
                ],
                [
                    'nom'  => 'asc'
                ]
            );
        }

        $classeTarif = [];

        foreach ($classes as $classe) {
            $classeTarif[$classe->getNom()] = $tarifRepository->findOneBy(["classe" => $classe]);
        }

        return $this->render('classes/index.html.twig', [
            'classes' => $classes,
            'niveaux' => ['primaire', 'college', 'lycee'],
            'active' => $trie,
            'classeTarif' => $classeTarif,
        ]);
    }

    #[Route('/bulletins', name: 'app_classes_bulletins')]
    public function bulletin(ElevesRepository $elevesRepository, AnneeScolaireRepository $anneeSR): Response
    {
        $anneeScolaire = $anneeSR->findOneBy(['actif' => 1]);
        $classes = $anneeScolaire->getClasses();

        $classeEleves = [];

        foreach ($classes as $classe) {
            $classeEleves[$classe->getNom()] = $elevesRepository->findBy([
                "classe" => $classe,
            ]);
        }

        return $this->render('classes/bulletins.html.twig', [
            'classeEleves' => $classeEleves,
        ]);
    }

    #[Route('/new', name: 'app_classes_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, AnneeScolaireRepository $anneeSR): Response
    {
        $anneeScolaire = $anneeSR->findOneBy(['actif' => 1]);
        $classe = new Classes();
        $tarif = new Tarif($anneeScolaire);

        $form = $this->createForm(ClassesType::class, $classe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $classe->setAnneeScolaire($anneeScolaire);
            $entityManager->persist($classe);

            // Working on Tarif
            $tarif->setPrixAnnuel($request->get("prix_annuel"));
            $tarif->setPrixInscription($request->get("prix_inscription"));
            $tarif->setPrixReinscription($request->get("prix_reinscription"));
            $tarif->setClasse($classe);
            $entityManager->persist($tarif);

            $entityManager->flush();

            $this->addFlash("success", "La classe a été ajoutée");
            return $this->redirectToRoute('app_classes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('classes/new.html.twig', [
            'class' => $classe,
            'form' => $form,
        ]);
    }
    // Contrôleur Symfony
    #[Route('/eleves/{classeId}', name: 'app_classes_eleves')]
    public function eleves(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository)
    {
        $classeId = $request->attributes->get('classeId');
        $classe = $classesRepository->find($classeId);

        if (!$classe) {
            return $this->json(['eleves' => []]);
        }

        $eleves = $elevesRepository->findBy(["classe" => $classe]);

        $eleveData = [];
        foreach ($eleves as $eleve) {
            $eleveData[] = [
                'id' => $eleve->getId(),
                'nom' => $eleve->getNom(),
                'classeId' => $classeId,
                'formAction' => $this->generateUrl('app_notes_bulletin_eleve', ['classe' => $classeId, 'eleve' => $eleve->getId()]) // Génération de l'URL

            ];
        }

        return $this->json(['eleves' => $eleveData]);
    }


    #[Route('/{id}', name: 'app_classes_show', methods: ['GET'])]
    public function show(Classes $class): Response
    {
        return $this->render('classes/show.html.twig', [
            'class' => $class,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_classes_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Classes $class, EntityManagerInterface $entityManager, TarifRepository $tarifRepository): Response
    {
        $form = $this->createForm(ClassesType::class, $class);
        $tarif = $tarifRepository->findOneBy(['classe' => $class]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Working on Tarif
            $tarif->setPrixAnnuel($request->get("prix_annuel"));
            $tarif->setPrixInscription($request->get("prix_inscription"));
            $tarif->setPrixReinscription($request->get("prix_reinscription"));
            $tarif->setClasse($class);
            $tarif->setAnneeScolaire($class->getAnneeScolaire());
            $entityManager->persist($tarif);

            
            $entityManager->flush();

            $this->addFlash("success", "La classe a été modifiée avec succès");
            return $this->redirectToRoute('app_classes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('classes/edit.html.twig', [
            'class' => $class,
            'form' => $form,
            'tarif' => $tarif,
        ]);
    }

    #[Route('/{id}', name: 'app_classes_delete', methods: ['POST'])]
    public function delete(Request $request, Classes $class, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $class->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($class);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_classes_index', [], Response::HTTP_SEE_OTHER);
    }
}
