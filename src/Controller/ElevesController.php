<?php

namespace App\Controller;

use App\Entity\Eleves;
use App\Entity\Parents;
use App\Entity\ParentsEleves;
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
    public function index(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository): Response
    {
        $trie = $request->get('trie');
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

    #[Route('/backup')]
    public function backup(EntityManagerInterface $entityManager, ClassesRepository $classesRepository, ElevesRepository $elevesRepository)
    {

        // $classe = $classesRepository->findOneBy(['nom' => '2nde CD']);
        // $new = $classesRepository->findOneBy(['nom' => '1ere A1']);
        // $find = $elevesRepository->findBy([
        //     'classe' => $classe->getId()
        // ]);

        // foreach ($find as $student) {

        //     $student->setClasse($new);

        //     $entityManager->persist($student);
        // }
        // $entityManager->flush();

        // return $this->redirectToRoute('app_eleves_index');
        $fichier = file_get_contents('C:\Users\DE\Documents\amenapp1\students_with_classrooms.json', 'students_with_classrooms.json');

        $students = json_decode($fichier, true);
        $i = 1;

        $j = 1;

        foreach ($students as $student) {


            $elefe = new Eleves();
            $pere = new Parents();
            $mere = new Parents();
            $parentsM = new ParentsEleves();
            $parentsP = new ParentsEleves();

            $find = $elevesRepository->findOneBy([
                'nom' => $student['lastname'],
                'prenom' => $student['firstname'],
            ]);

            if (!$find) {

                $elefe->setNom($student['lastname']);
                $elefe->setPrenom($student['firstname']);
                $elefe->setSexe($student['gender']);

                if ($student["classroom"] == '6Ã¨') {
                    $classe = $classesRepository->findOneBy(['nom' => '5e']);
                    $elefe->setClasse($classe);
                } elseif ($student["classroom"] == '5Ã¨') {
                    $classe = $classesRepository->findOneBy(['nom' => '4e']);
                    $elefe->setClasse($classe);
                } elseif ($student["classroom"] == '4Ã¨') {
                    $classe = $classesRepository->findOneBy(['nom' => '3e']);
                    $elefe->setClasse($classe);
                } elseif ($student["classroom"] == '3Ã¨') {
                    $classe = $classesRepository->findOneBy(['nom' => '2nde AB']);
                    $elefe->setClasse($classe);
                } elseif ($student["classroom"] == '1Ã¨re B') {
                    $classe = $classesRepository->findOneBy(['nom' => 'Tle B']);
                    $elefe->setClasse($classe);
                } elseif ($student["classroom"] == '1ere D') {
                    $classe = $classesRepository->findOneBy(['nom' => 'Tle D']);
                    $elefe->setClasse($classe);
                } elseif ($student["classroom"] == '1ere A2') {
                    $classe = $classesRepository->findOneBy(['nom' => 'Tle A2']);
                    $elefe->setClasse($classe);
                } else {
                    $classe = $classesRepository->findOneBy(['nom' => $student["classroom"]]);
                    $elefe->setClasse($classe);
                }

                    $entityManager->persist($elefe);

                $pere->setNom("");
                $pere->setTelephone("");
                $pere->setProfession("");
                $pere->setType("pere");
                $entityManager->persist($pere);

                $mere->setNom("");
                $mere->setTelephone("");
                $mere->setProfession("");
                $mere->setType("mere");
                $entityManager->persist($mere);

                $parentsM->setEleve($elefe);
                $parentsM->setParent($pere);
                $entityManager->persist($parentsP);

                $parentsP->setEleve($elefe);
                $parentsP->setParent($mere);
                $entityManager->persist($parentsM);

                $i++;

                if ($i >= 20) {
                    $entityManager->flush();
                    $entityManager->clear();
                    $i = 0;
                }
            }else{
                if($j >= 131 and $j <= 141 and $find->getClasse()->getNom() == '2nde AB'){
                    $classe = $classesRepository->findOneBy(['nom' => '1ere A1']);
                    $find->setClasse($classe);
                    $entityManager->persist($find);
                }
                if($j > 141 and $j <= 151 and $find->getClasse()->getNom() == '2nde AB'){
                    $classe = $classesRepository->findOneBy(['nom' => '1ere D']);
                    $find->setClasse($classe);
                    $entityManager->persist($find);
                }
            }
            dump($j);
            $j += 1;
        }
        $entityManager->flush();

        return $this->redirectToRoute('app_eleves_index');
    }


    #[Route('/new', name: 'app_eleves_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
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

            $this->addFlash("success", "Nouvel élève ajouté");
            return $this->redirectToRoute('app_eleves_new', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('eleves/new.html.twig', [
            'elefe' => $elefe,
            'form' => $form,
        ]);
    }

    #[Route('/renew', name: 'app_eleves_renew', methods: ['GET', 'POST'])]
    public function renew(Request $request, ElevesRepository $elevesRepository, ClassesRepository $classesRepository): Response
    {
        $trie = $request->get('trie');
        if (!$trie or $trie == "all") {
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

    #[Route('/{id}', name: 'app_eleves_show', methods: ['GET'])]
    public function show(Eleves $elefe): Response
    {
        return $this->render('eleves/show.html.twig', [
            'elefe' => $elefe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_eleves_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Eleves $elefe, EntityManagerInterface $entityManager, ClassesRepository $classesRepository): Response
    {
        $pere = new Parents();
        $mere = new Parents();
        $parentsM = new ParentsEleves();
        $parentsP = new ParentsEleves();

        $form = $this->createForm(Eleves1Type::class, $elefe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $elefe->setClasse($classesRepository->findOneBy(["id" => $request->get("classse")]));
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

            $this->addFlash("success", "L'élève a été modifié");
            return $this->redirectToRoute('app_eleves_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('eleves/edit.html.twig', [
            'elefe' => $elefe,
            'form' => $form,
            'edit' => 'edit',
            'classes' => $classesRepository->findAll(),
            'active' => $elefe->getClasse()->getId(),
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
