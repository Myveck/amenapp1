<?php

namespace App\Controller;

use App\Entity\Eleves;
use App\Entity\ElevesBackup;
use App\Entity\Parents;
use App\Entity\ParentsEleves;
use App\Form\Eleves1Type;
use App\Repository\ClassesRepository;
use App\Repository\EcolesRepository;
use App\Repository\ElevesBackupRepository;
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
    public function index(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository): Response
    {
        $trie = $request->get('trie');
        $annee_actuelle = '2024-2025';
        if ($trie == "all" or !$trie) {
            $eleves = $elevesRepository->findBy([], ['nom' => 'asc']);
            $trie = "all";
        } else {
            $eleves = $elevesRepository->findBy(['classe' => $trie], ['nom' => 'asc']);
        }
        return $this->render('eleves/index.html.twig', [
            'eleves' => $eleves,
            'classes' => $classesRepository->findBy([], ['classeOrder' => 'asc']),
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

    #[Route('/{annee}/renew', name: 'app_eleves_renew', methods: ['GET', 'POST'])]
    public function renew(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository): Response
    {
        $anneeScolaire = $request->get('annee');
        $trie = $request->get('trie');
        if (!$trie or $trie == "all") {
            $eleves = $elevesRepository->findBy([], ['nom' => 'asc'], 30);
        } else {
            $eleves = $elevesRepository->findBy(['classe' => $trie], ['nom' => 'asc']);
        }
        return $this->render('eleves/renew.html.twig', [
            'eleves' => $eleves,
            'classes' => $classesRepository->findBy([], ['classeOrder' => 'asc']),
            'active' => $trie,
            'annee_actuelle' => $anneeScolaire,
        ]);
    }

    #[Route('/new', name: 'app_eleves_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ElevesBackupRepository $elevesBackupRepository, EcolesRepository $ecolesRepository): Response
    {
        $ecole = $ecolesRepository->findOneBy(['id' => 1]);
        $anneeScolaire = $ecole->getAnneeScolaire()->getAnnee();
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

            // Gestion du backup
            $eleveBackup = new ElevesBackup();
            $verif = $elevesBackupRepository->findOneBy([
                "name" => $elefe->getNom() . ' ' . $elefe->getPrenom(),
                "classe" => $elefe->getClasse()->getNom()
            ]);

            if (!$verif) {
                $eleveBackup->setName($elefe->getNom() . ' ' . $elefe->getPrenom());
                $eleveBackup->setClasse($elefe->getClasse()->getNom());
                $eleveBackup->setAnneeScolaire($anneeScolaire);
                $entityManager->persist($eleveBackup);
            }


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
    public function edit(Request $request, Eleves $elefe, EntityManagerInterface $entityManager, ClassesRepository $classesRepository, ElevesBackupRepository $elevesBackupRepository, EcolesRepository $ecolesRepository): Response
    {
        $anneeScolaire = $ecolesRepository->findOneBy(['id' => 1])->getAnneeScolaire()->getAnnee();
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

            // Gestion du backup
            $eleveBackup = $elevesBackupRepository->findOneBy([
                "name" => $elefe->getNom() . ' ' . $elefe->getPrenom(),
                "classe" => $elefe->getClasse()->getNom()
            ]);
            if ($eleveBackup) {
                $eleveBackup->setName($elefe->getNom() . ' ' . $elefe->getPrenom());
                $eleveBackup->setClasse($elefe->getClasse()->getNom());
                $eleveBackup->setAnneeScolaire($anneeScolaire);
                $entityManager->persist($eleveBackup);
            } else {
                // Si l'élève n'a pas été backed up, on le backup
                $eleveBackup = new ElevesBackup();
                $eleveBackup->setName($elefe->getNom() . ' ' . $elefe->getPrenom());
                $eleveBackup->setClasse($elefe->getClasse()->getNom());
                $eleveBackup->setAnneeScolaire($anneeScolaire);
                $entityManager->persist($eleveBackup);
            }

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
