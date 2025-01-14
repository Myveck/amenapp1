<?php

namespace App\Controller;

use App\Repository\AnneeScolaireRepository;
use App\Repository\ClassesBackupRepository;
use App\Repository\ClassesRepository;
use App\Repository\EcolesRepository;
use App\Repository\ElevesBackupRepository;
use App\Repository\ElevesRepository;
use App\Repository\EnseignantsRepository;
use App\Repository\MatieresRepository;
use App\Repository\TarifRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(EcolesRepository $ecolesRepository): Response
    {
        $schoolYear = $ecolesRepository->findOneBy(['id' => 1])->getAnneeScolaire()->getAnnee();

        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'annee_actuelle' => $schoolYear
        ]);
    }

    #[Route('/ecole', name: 'app_main_ecole')]
    public function ecole(
        ElevesRepository $elevesRepository,
        ClassesRepository $classesRepository,
        MatieresRepository $matieresRepository,
        EnseignantsRepository $enseignantsRepository,
    ): Response {
        $eleves = $elevesRepository->findAll();
        $classes = $classesRepository->findBy([], ["classeOrder" => 'asc']);
        $matieres = $matieresRepository->findAll();
        $ensignants = $enseignantsRepository->findAll();

        $m = count($elevesRepository->findBy(["sexe" => "m"]));
        $f = count($elevesRepository->findBy(["sexe" => "f"]));

        $nombre = [];
        foreach ($classes as $classe) {
            $nombre[$classe->getNom()] = count($classe->getEleves());
        }
        // dd($nombre);



        return $this->render('main/ecole.html.twig', [
            'eleves' => count($eleves),
            'classes' => count($classes),
            'matieres' => count($matieres),
            'enseignants' => count($ensignants),
            'masculins' => $m,
            'feminins' => $f,
            'nombreParClasse' => $nombre,
        ]);
    }

    #[Route('/finances', name: 'app_main_finances', methods: ['GET', 'POST'])]
    public function finance(
        Request $request,
        ElevesRepository $elevesRepository,
        ClassesRepository $classesRepository,
        TarifRepository $tarifRepository,
        AnneeScolaireRepository $anneeScolaireRepository,
        EcolesRepository $ecolesRepository,
        ElevesBackupRepository $elevesBackupRepository,
        ClassesBackupRepository $classesBackupRepository,
    ) {
        $tarifInscriptionParClasse = [];
        $tarifAnnuelParClasse = [];
        $elevesParClasse = [];

        if (!$request->get('annee')) {
            $active = $ecolesRepository->findOneBy(['id' => 1])->getAnneeScolaire();
            $eleves = $elevesRepository->findAll();
            $classes = $classesRepository->findAll();
            $total = count($elevesRepository->findAll());
        } else {
            $active = $anneeScolaireRepository->findOneBy(['id' => $request->get('annee')]);
            $ecoleAS = $ecolesRepository->findOneBy(['id' => 1])->getAnneeScolaire();
            $eleves = $elevesRepository->findAll();

            if ($ecoleAS->getId() == $active->getId()) {
                $classes = $classesRepository->findAll();
                $total = count($elevesRepository->findAll());
            } else {
                $eleves = $elevesBackupRepository->findBy(['anneeScolaire' => $active->getAnnee()]);
                $classes = $classesBackupRepository->findBy(['anneeScolaire' => $active]);
                $total = count($elevesBackupRepository->findBy([
                    'anneeScolaire' => $active,
                ]));
            }
        }

        if ($classes) {
            foreach ($classes as $classe) {
                $tarifInscriptionParClasse[$classe->getId()] = $tarifRepository->findOneBy(['classe' => $classe])->getPrixInscription();
                $tarifAnnuelParClasse[$classe->getId()] =
                    $tarifRepository->findOneBy(['classe' => $classe])->getPrixAnnuel();
                $elevesParClasse[$classe->getId()] = count($elevesRepository->findBy(['classe' => $classe]));
            }

            return $this->render('main/finance.html.twig', [
                'total' => $total,
                'classes' => $classes,
                'eleves' => $eleves,
                'tarifInscriptionParClasse' => $tarifInscriptionParClasse,
                'tarifAnnuelParClasse' => $tarifAnnuelParClasse,
                'elevesParClasse' => $elevesParClasse,
                'anneesScolaires' => $anneeScolaireRepository->findAll(),
                'active' => $active,
                'affiche' => 1
            ]);
        } else {
            $active = $anneeScolaireRepository->findOneBy(['id' => $request->get('annee')]);
            return $this->render('main/finance.html.twig', [
                'anneesScolaires' => $anneeScolaireRepository->findAll(),
                'active' => $active,
            ]);
        }
    }
}
