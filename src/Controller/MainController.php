<?php

namespace App\Controller;

use App\Repository\ClassesRepository;
use App\Repository\ElevesRepository;
use App\Repository\EnseignantsRepository;
use App\Repository\MatieresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(): Response
    {
        $next = date("Y") + 1;
        $year = date("Y");

        $schoolYear = strval($year) . '-' . strval($next);
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

        $m = $elevesRepository->findBy(["sexe" => "m"]);
        $f = $elevesRepository->findBy(["sexe" => "f"]);

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
            'masculins' => count($m),
            'feminins' => count($f),
            'nombreParClasse' => $nombre,
        ]);
    }
}
