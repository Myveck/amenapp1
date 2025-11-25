<?php

namespace App\DataFixtures;

use App\Entity\AnneeScolaire;
use App\Entity\Classes;
use App\Entity\ClassesMatieres;
use App\Entity\Eleves;
use App\Entity\Evaluations;
use App\Entity\Examinations;
use App\Entity\Inscription;
use App\Entity\Matieres;
use App\Entity\Notes;
use App\Entity\Tarif;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class BulletinTestFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        
        $faker = Factory::create('fr_FR');

        // 1️⃣ Année scolaire
        $anneeScolaire = new AnneeScolaire();
        $anneeScolaire->setActif(true);
        $anneeScolaire->setAnnee('2025-2026');
        $manager->persist($anneeScolaire);

        
        // 6️⃣ Évaluations
        $evalName = ["D1", "D2", "MI", "DH"];
        $evaluations = [];
        foreach ($evalName as $name) {
            $evaluation = new Evaluations();
            $evaluation->setAnneeScolaire($anneeScolaire);
            $evaluation->setNom($name);
            $manager->persist($evaluation);
            $evaluations[] = $evaluation;
        }

        // 2️⃣ Créer plusieurs classes
        $classesData = [
            ['nom' => '6ème', 'niveau' => 'college', 'ordre' => 1],
            ['nom' => '5ème', 'niveau' => 'college', 'ordre' => 2],
            ['nom' => '4ème', 'niveau' => 'college', 'ordre' => 3],
            ['nom' => '3ème', 'niveau' => 'college', 'ordre' => 4],
        ];

        $nomsMatieres = ['Maths', 'Français', 'Physique', 'SVT'];
        $trimestres = [1, 2, 3];

        foreach ($classesData as $classData) {

            // 3️⃣ Classe
            $classe = new Classes();
            $classe->setNom($classData['nom']);
            $classe->setNiveau($classData['niveau']);
            $classe->setClasseOrder($classData['ordre']);
            $classe->setAnneeScolaire($anneeScolaire);
            $manager->persist($classe);

            // Tarifs de la classe
            $tarif = new Tarif($anneeScolaire);
            $tarif->setClasse($classe);
            $tarif->setPrixAnnuel(100000);
            $tarif->setPrixInscription(20000);
            $tarif->setPrixReinscription(12000);
            $manager->persist($tarif);

            // 4️⃣ Matières, ClassesMatieres et Examinations
            $matieresData = [];
            foreach ($nomsMatieres as $nom) {
                $matiere = new Matieres();
                $matiere->setNom($nom);
                $manager->persist($matiere);

                $classeMatiere = new ClassesMatieres();
                $classeMatiere->setClasse($classe);
                $classeMatiere->setMatiere($matiere);
                $classeMatiere->setAnneeScolaire($anneeScolaire);
                $classeMatiere->setCoefficient($faker->numberBetween(1, 5));
                $manager->persist($classeMatiere);

                foreach ($trimestres as $trim) {
                    $examination = new Examinations();
                    $examination->setClasse($classe);
                    $examination->setAnneeScolaire($anneeScolaire);
                    $examination->setMatiere($matiere);
                    $examination->setTrimestre($trim);
                    $manager->persist($examination);

                    $matieresData[] = [
                        'matiere' => $matiere,
                        'classeMatiere' => $classeMatiere,
                        'examination' => $examination,
                        'trimestre' => $trim
                    ];
                }
            }

            // 5️⃣ Élèves
            $eleves = [];
            $sexe = ['m', 'f'];
            for ($i = 0; $i < 5; $i++) {
                $eleve = new Eleves();
                $eleve->setNom($faker->lastName);
                $eleve->setPrenom($faker->firstName);
                $eleve->setSexe($sexe[rand(0, 1)]);
                $eleve->setAnneeScolaire($anneeScolaire);
                $manager->persist($eleve);

                $inscription = new Inscription();
                $inscription->setEleve($eleve);
                $inscription->setClasse($classe);
                $inscription->setAnneeScolaire($anneeScolaire);
                $inscription->setActif(true);
                $inscription->setDateInscription(new DateTime());
                $manager->persist($inscription);

                $eleves[] = $eleve;
            }


            // 7️⃣ Notes aléatoires
            foreach ($eleves as $eleve) {
                foreach ($matieresData as $data) {
                    foreach ($evaluations as $evaluation) {
                        $note = new Notes();
                        $note->setEleveId($eleve);
                        $note->setEvaluation($evaluation);
                        $note->setExaminations($data['examination']);
                        $note->setDateEvaluation(new DateTime());
                        $note->setNote($faker->randomFloat(2, 5, 20)); // notes réalistes
                        $manager->persist($note);
                    }
                }
            }
        }

        $manager->flush();
    }
}
