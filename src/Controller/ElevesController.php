<?php

namespace App\Controller;

use App\Entity\Eleves;
use App\Entity\ElevesBackup;
use App\Entity\Parents;
use App\Entity\ParentsEleves;
use App\Form\Eleves1Type;
use App\Repository\AnneeScolaireRepository;
use App\Repository\ClassesRepository;
use App\Repository\EcolesRepository;
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
    public function index(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository, AnneeScolaireRepository $anneeSR): Response
    {
        $trie = $request->get('trie');
        $annee_actuelle = $anneeSR->findOneBy(["actif" => 1]);
        if ($trie == "all" or !$trie) {
            $eleves = $annee_actuelle->getEleves();
            $trie = "all";
        } else {
            $eleves = $elevesRepository->findBy(['classe' => $trie], ['nom' => 'asc']);
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
    public function paiement(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository): Response
    {
        $trie = $request->get('trie');
        if ($trie == "all" or !$trie) {
            $eleves = $elevesRepository->findBy([], ['nom' => 'asc']);
            $trie = "all";
        } else {
            $eleves = $elevesRepository->findBy(['classe' => $trie], ['nom' => 'asc']);
        }
        return $this->render('paiements/eleves.html.twig', [
            'eleves' => $eleves,
            'classes' => $classesRepository->findBy([], ['classeOrder' => 'asc']),
            'active' => $trie,
            'nombre' => count($eleves),
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

    #[Route('/renew', name: 'app_eleves_renew', methods: ['GET', 'POST'])]
    public function renew(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository, AnneeScolaireRepository $anneeSR, InscriptionRepository $inscriptionRep): Response
    {
        $anneeScolaire = $anneeSR->findOneBy(["actif" => 1]);
        $trie = $request->get('trie');
        if (!$trie or $trie == "all") {
            $eleves = $inscriptionRep->findElevesByAnneeActuelle();
        } else {
            $eleves = $inscriptionRep->findElevesActuelsByClasse($trie);
        }
        return $this->render('eleves/renew.html.twig', [
            'eleves' => $eleves,
            'classes' => $classesRepository->findBy([], ['classeOrder' => 'asc']),
            'active' => $trie,
            'annee_actuelle' => $anneeScolaire,
        ]);
    }

    #[Route('/new', name: 'app_eleves_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, AnneeScolaireRepository $anneeSR): Response
    {
        $anneeScolaire = $anneeSR->findOneBy(["actif" => 1]);
        $elefe = new Eleves();
        $pere = new Parents();
        $mere = new Parents();
        $parentsM = new ParentsEleves();
        $parentsP = new ParentsEleves();
        $form = $this->createForm(Eleves1Type::class, $elefe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($elefe);

            $pere->setNom($request->get("pere_nom"));
            $pere->setTelephone($request->get("pere_telephone"));
            $pere->setProfession($request->get("pere_profession"));
            $pere->setType("pere");
            $entityManager->persist($pere);

            $mere->setNom($request->get("mere_nom"));
            $mere->setTelephone($request->get("mere_telephone"));
            $mere->setProfession($request->get("mere_profession"));
            $mere->setType("mere");
            $entityManager->persist($mere);

            $parentsM->setEleve($elefe);
            $parentsM->setParent($pere);
            $entityManager->persist($parentsP);

            $parentsP->setEleve($elefe);
            $parentsP->setParent($mere);
            $entityManager->persist($parentsM);

            $entityManager->flush();

            $this->addFlash("success", "Nouvel élève ajouté avec succès");
            return $this->redirectToRoute('app_eleves_new', ['annee_actuelle' => $anneeScolaire,], Response::HTTP_SEE_OTHER);
        }

        return $this->render('eleves/new.html.twig', [
            'elefe' => $elefe,
            'form' => $form,
            'annee_actuelle' => $anneeScolaire,
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
    public function edit(Request $request, Eleves $elefe, EntityManagerInterface $entityManager, ClassesRepository $classesRepository, ElevesBackupRepository $elevesBackupRepository, AnneeScolaireRepository $anneeSR): Response
    {
        $anneeScolaire = $anneeSR->findOneBy(["actif" => 1]);
        $pere = new Parents();
        $mere = new Parents();
        $parentsM = new ParentsEleves();
        $parentsP = new ParentsEleves();
        $trie = $elefe->getClasse()->getId();

        $form = $this->createForm(Eleves1Type::class, $elefe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($elefe);

            $pere->setNom($request->get("pere_nom"));
            $pere->setTelephone($request->get("pere_telephone"));
            $pere->setProfession($request->get("pere_profession"));
            $pere->setType("pere");
            $entityManager->persist($pere);

            $mere->setNom($request->get("mere_nom"));
            $mere->setTelephone($request->get("mere_telephone"));
            $mere->setProfession($request->get("mere_profession"));
            $mere->setType("mere");
            $entityManager->persist($mere);

            $parentsM->setEleve($elefe);
            $parentsM->setParent($pere);
            $entityManager->persist($pere);

            $parentsP->setEleve($elefe);
            $parentsP->setParent($mere);
            $entityManager->persist($mere);

            $entityManager->flush();

            $this->addFlash("success", "L'élève a été modifié avec succès");
            return $this->redirectToRoute('app_eleves_index', ["trie" => $trie], Response::HTTP_SEE_OTHER);
        }

        return $this->render('eleves/edit.html.twig', [
            'elefe' => $elefe,
            'form' => $form,
            'edit' => 'edit',
            'classes' => $classesRepository->findAll(),
            'active' => $elefe->getClasse()->getId(),
            'annee_actuelle' => $anneeScolaire,
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

        $this->addFlash("success", "L'élève a été supprimé avec succès");
        return $this->redirectToRoute('app_eleves_index', ["trie" => $elefe->getClasse()->getId()], Response::HTTP_SEE_OTHER);
    }
}
