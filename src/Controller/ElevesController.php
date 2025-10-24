<?php

namespace App\Controller;

use App\Entity\Eleves;
use App\Entity\Inscription;
use App\Entity\Parents;
use App\Entity\ParentsEleves;
use App\Form\Eleves1Type;
use App\Form\InscriptionType;
use App\Repository\AnneeScolaireRepository;
use App\Repository\ClassesRepository;
use App\Repository\ElevesBackupRepository;
use App\Repository\ElevesRepository;
use App\Repository\InscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/eleves')]
final class ElevesController extends AbstractController
{
    #[Route(name: 'app_eleves_index', methods: ['GET'])]
    public function index(Request $request, ClassesRepository $classesRepository, AnneeScolaireRepository $anneeSR, InscriptionRepository $inscriptionRepo): Response
    {
        $trie = $request->get('trie');
        $annee_actuelle = $anneeSR->findOneBy(["actif" => 1]);
        dd($annee_actuelle);
        if ($trie == "all" or !$trie) {
            $eleves = $inscriptionRepo->findElevesByAnneeActuelle();
            $trie = "all";
        } else {
            $classe = $classesRepository->find($trie);
            $eleves = $inscriptionRepo->findElevesActuelsByClasse($classe);
        }
        return $this->render('eleves/index.html.twig', [
            'eleves' => $eleves,
            'classes' => $classesRepository->findBy(["annee_scolaire" => $annee_actuelle], ['classeOrder' => 'asc']),
            'active' => $trie,
            'nombre' => count($eleves),
            'annee_actuelle' => $annee_actuelle
        ]);
    }

    #[Route('/paiments', name: 'app_eleves_paiements', methods: ['GET'])]
    public function paiement(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository, InscriptionRepository $inscriptionRepo): Response
    {
        $trie = $request->get('trie');
        if ($trie == "all" or !$trie) {
            $eleves = $elevesRepository->findBy([], ['nom' => 'asc']);
            $trie = "all";
        } else {
            $classe = $classesRepository->find($trie);
            $eleves = $inscriptionRepo->findElevesActuelsByClasse($trie);
        }
        return $this->render('paiements/eleves.html.twig', [
            'eleves' => $eleves,
            'classes' => $classesRepository->findBy([], ['classeOrder' => 'asc']),
            'active' => $trie,
            'nombre' => count($eleves),
        ]);
    }

    #[Route('/note', name: 'app_eleves_note', methods: ['GET'])]
    public function note(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository, InscriptionRepository $inscriptionRepo): Response
    {
        $trie = $request->get('trie');
        return $this->render('eleves/index.html.twig', [
            'eleves' => $inscriptionRepo->findElevesActuelsByClasse($trie),
            'classes' => $classesRepository->findByAnneeActuelleOrdered(),
            'active' => 'all',
        ]);
    }

    #[Route('/trier', name: 'app_eleves_trier', methods: ['GET'])]
    public function trier(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository, InscriptionRepository $inscriptionRepo): Response
    {
        $trie = $request->get('trie');
        if ($trie == "all") {
            $eleves = $elevesRepository->findBy([], ['nom' => 'asc']);
        } else {
            $classe = $classesRepository->find($trie);
            $eleves = $inscriptionRepo->findElevesActuelsByClasse($classe);
        }
        return $this->render('eleves/index.html.twig', [
            'eleves' => $eleves,
            'classes' => $classesRepository->findByAnneeActuelleOrdered(),
            'active' => $trie,
        ]);
    }

    #[Route('/choice', name: 'app_eleves_choice', methods: ['GET'])]
    public function choice(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository, InscriptionRepository $inscriptionRepo): Response
    {
        $trie = $request->get('trie');
        if (!$trie or $trie == "all") {
            $eleves = $elevesRepository->findBy([], ['nom' => 'asc']);
        } else {
            $classe = $classesRepository->find($trie);
            $eleves = $inscriptionRepo->findElevesActuelsByClasse($classe);
        }
        return $this->render('eleves/choice.html.twig', [
            'eleves' => $eleves,
            'classes' => $classesRepository->findByAnneeActuelleOrdered(),
            'active' => $trie,
        ]);
    }

    #[Route('/renew', name: 'app_eleves_renew', methods: ['GET', 'POST'])]
    public function renew(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository, AnneeScolaireRepository $anneeSR, InscriptionRepository $inscriptionRep): Response
    {
        $anneeScolaire = $anneeSR->findOneBy(["actif" => 1]);
        $trie = $request->get('trie');
        if (!$trie or $trie == "all") {
            $eleves = $inscriptionRep->findElevesByAnneeActuelle();
        } else {
            $classe = $classesRepository->findOneBy(["id" => $trie]);
            $eleves = $inscriptionRep->findElevesActuelsByClasse($classe);
        }
        return $this->render('eleves/renew.html.twig', [
            'eleves' => $eleves,
            'classes' => $classesRepository->findByAnneeActuelleOrdered(),
            'active' => $trie,
            'annee_actuelle' => $anneeScolaire,
        ]);
    }

    #[Route('/new', name: 'app_eleves_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, AnneeScolaireRepository $anneeSR): Response
    {
        $anneeScolaire = $anneeSR->findOneBy(["actif" => true]);

        $eleve = new Eleves();
        $eleve->setAnneeScolaire($anneeScolaire);

        // Create the first inscription
        $inscription = new Inscription();
        $inscription->setAnneeScolaire($anneeScolaire);
        $inscription->setRedouble(false);
        $inscription->setActif(true);
        
        // Add inscription to the collection
        $eleve->addInscription($inscription);

        $form = $this->createForm(Eleves1Type::class, $eleve);
        $form->handleRequest($request);

        $formInscription = $this->createForm(InscriptionType::class, $inscription);
        $formInscription->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Thanks to cascade: ['persist'], only persist $eleve
            $entityManager->persist($eleve);
            $entityManager->flush();

            $this->addFlash("success", "Nouvel élève ajouté avec succès");
            return $this->redirectToRoute('app_eleves_new', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('eleves/new.html.twig', [
            'eleve' => $eleve,
            'formEleve' => $form,
            'formInscription' => $formInscription,
            'annee_actuelle' => $anneeScolaire->getAnnee(),
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
    public function edit(Request $request, Eleves $elefe, EntityManagerInterface $entityManager, ClassesRepository $classesRepository,  AnneeScolaireRepository $anneeSR): Response
    {
        $anneeScolaire = $anneeSR->findOneBy(["actif" => 1]);
        $trie = $elefe->getClasseActuelle()->getId();

        $form = $this->createForm(Eleves1Type::class, $elefe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($elefe);

            $entityManager->flush();

            $this->addFlash("success", "L'élève a été modifié avec succès");
            return $this->redirectToRoute('app_eleves_index', ["trie" => $trie], Response::HTTP_SEE_OTHER);
        }

        return $this->render('eleves/edit.html.twig', [
            'elefe' => $elefe,
            'form' => $form,
            'edit' => 'edit',
            'classes' => $classesRepository->findByAnneeActuelleOrdered(),
            'active' => $elefe->getClasseActuelle()->getId(),
            'annee_actuelle' => $anneeScolaire,
        ]);
    }

    #[Route('/{id}/promotion', name: 'app_eleves_promotion', methods: ['GET', 'POST'])]
    public function promotion(Request $request, Eleves $elefe, EntityManagerInterface $entityManager, AnneeScolaireRepository $anneeScolaireRepo): Response
    {
        $anneeScolaire = $anneeScolaireRepo->findOneBy(['actif' => true]);
        // Create the first inscription
        $inscription = new Inscription();
        $inscription->setAnneeScolaire($anneeScolaire);
        $inscription->setRedouble(false);
        $inscription->setActif(true);
        
        // Add inscription to the collection
        $elefe->addInscription($inscription);

        $form = $this->createForm(Eleves1Type::class, $elefe);
        $form->handleRequest($request);

        
        $formInscription = $this->createForm(InscriptionType::class, $inscription);
        $formInscription->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_eleves_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('eleves/promotion.html.twig', [
            'elefe' => $elefe,
            'formEleve' => $form,
            'formInscription' => $formInscription,
            'annee_actuelle' => $anneeScolaire->getAnnee(),
        ]);
    }

    #[Route('/{id}', name: 'app_eleves_delete', methods: ['POST'])]
    public function delete(Request $request, Eleves $elefe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $elefe->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($elefe);
            $entityManager->flush();
        }

        $this->addFlash("success", "L'élève a été supprimé avec succès");
        return $this->redirectToRoute('app_eleves_index', ["trie" => $elefe->getClasseActuelle()->getId()], Response::HTTP_SEE_OTHER);
    }
}
