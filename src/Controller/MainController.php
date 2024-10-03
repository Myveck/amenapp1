<?php

namespace App\Controller;

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
}
