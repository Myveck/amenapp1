<?php

namespace App\Service;

use App\Entity\AnneeScolaire;
use App\Entity\Classes;
use App\Entity\ClassesMatieres;
use App\Entity\Inscription;
use App\Entity\Tarif;
use App\Repository\AnneeScolaireRepository;
use App\Repository\ClassesRepository;
use App\Repository\InscriptionRepository;
use App\Repository\TarifRepository;
use Doctrine\ORM\EntityManagerInterface;

class AnneeManager
{
    private EntityManagerInterface $em;
    private AnneeScolaireRepository $anneeRepo;
    private ClassesRepository $classeRepo;
    private InscriptionRepository $inscriptionRepo;
    private BulletinManager $bulletinManager;
    private TarifRepository $tarifRepo;

    public function __construct(
        EntityManagerInterface $em,
        AnneeScolaireRepository $anneeRepo,
        ClassesRepository $classeRepo,
        InscriptionRepository $inscriptionRepo,
        BulletinManager $bulletinManager,
        TarifRepository $tarifRepo
    ) {
        $this->em = $em;
        $this->anneeRepo = $anneeRepo;
        $this->classeRepo = $classeRepo;
        $this->inscriptionRepo = $inscriptionRepo;
        $this->bulletinManager = $bulletinManager;
        $this->tarifRepo = $tarifRepo;
    }

    /**
     * Effectue le passage à la nouvelle année scolaire :
     * - Crée la nouvelle année
     * - Duplique les classes et matières
     * - Calcule les moyennes
     * - Réinscrit les élèves selon leur résultat
     */
    public function passerAnneeSuivante(float $seuil = 9.50): void
    {
        // 1️⃣ Récupérer l’année active
        $anneeActuelle = $this->anneeRepo->findOneBy(['actif' => true]);
        if (!$anneeActuelle) {
            throw new \RuntimeException('Aucune année scolaire active trouvée.');
        }

        // 2️⃣ Créer la nouvelle année
        $nouvelleAnnee = new AnneeScolaire();
        $nouvelleAnnee->setAnnee($this->genererNomNouvelleAnnee($anneeActuelle->getAnnee()));
        $nouvelleAnnee->setActif(true);
        $this->em->persist($nouvelleAnnee);


        // Désactiver l’ancienne année
        $anneeActuelle->setActif(false);

        // 3️⃣ Dupliquer les classes
        $classesActuelles = $this->classeRepo->findBy(['annee_scolaire' => $anneeActuelle]);

        foreach ($classesActuelles as $classe) {
            $nouvelleClasse = new Classes();
            $nouveauTarif = new Tarif($nouvelleAnnee);
            $tarifActuel = $this->tarifRepo->findOneBy(['classe'  => $classe]);

            $nouvelleClasse
                ->setNom($classe->getNom())
                ->setNiveau($classe->getNiveau())
                ->setClasseOrder($classe->getClasseOrder())
                ->setAnneeScolaire($nouvelleAnnee);

            $this->em->persist($nouvelleClasse);

            // Dupliquer les tarifs
            $nouveauTarif
                ->setClasse($nouvelleClasse)
                ->setPrixInscription($tarifActuel->getPrixInscription())
                ->setPrixReinscription($tarifActuel->getPrixReinscription())
                ->setPrixAnnuel($tarifActuel->getPrixAnnuel())
                ->setClasse($nouvelleClasse);
            $this->em->persist($nouveauTarif);

            // Dupliquer les matières liées à cette classe
            foreach ($classe->getClassesMatieres() as $cm) {
                $newCM = new ClassesMatieres();
                $newCM
                    ->setClasse($nouvelleClasse)
                    ->setMatiere($cm->getMatiere())
                    ->setAnneeScolaire($nouvelleAnnee)
                    ->setCoefficient($cm->getCoefficient());
                $this->em->persist($newCM);

            }
        }

        $this->em->flush();

        // Ajouter les classes suivantes dans les classes

        // 4️⃣ Créer les nouvelles inscriptions selon les moyennes
        foreach ($classesActuelles as $classe) {
            $inscriptions = $this->inscriptionRepo->findBy([
                'classe' => $classe,
                'AnneeScolaire' => $anneeActuelle,
                'actif' => true,
            ]);

            if (empty($inscriptions)) continue;

            $eleves = array_map(fn($i) => $i->getEleve(), $inscriptions);
            $moyennes = $this->bulletinManager->calculateAnnuelle($classe, $eleves);

            foreach ($inscriptions as $inscription) {
                $eleve = $inscription->getEleve();
                $moyenne = $moyennes[$eleve->getId()] ?? 0;
                $decision = $this->bulletinManager->getDecisionPassage($moyenne, $seuil);

                $ancienneClasse = $inscription->getClasse();
                $nouvelleClasse = null;

                // 🔹 Trouver la classe suivante à partir du champ nextClasse
                if ($ancienneClasse->getNextClasse()) {
                    // On récupère la copie de cette classe dans la nouvelle année
                    $next = $ancienneClasse->getNextClasse();
                    $nouvelleClasse = $this->classeRepo->findOneBy([
                        'nom' => $next->getNom(),
                        'annee_scolaire' => $nouvelleAnnee
                    ]); 
                }

                // 🔹 Si pas de classe suivante (ex : terminale), ou redoublement
                if (!$nouvelleClasse || $decision === 'redouble') {
                    $nouvelleClasse = $this->classeRepo->findOneBy([
                        'nom' => $ancienneClasse->getNom(),
                        'annee_scolaire' => $nouvelleAnnee
                    ]);
                }

                if (!$nouvelleClasse) continue;

                // 🔹 Créer la nouvelle inscription
                $nouvelleInscription = new Inscription();
                $nouvelleInscription
                    ->setEleve($eleve)
                    ->setClasse($nouvelleClasse)
                    ->setAnneeScolaire($nouvelleAnnee)
                    ->setMoyenneAnnuelle($moyenne)
                    ->setRedouble($decision === 'redouble')
                    ->setActif(true)
                    ->setDateInscription(new \DateTime());

                $this->em->persist($nouvelleInscription);

                // Désactiver l’ancienne inscription
                $inscription->setActif(false);
            }
        }

        // 5️⃣ Sauvegarde finale
        $this->em->flush();
    }


    /**
     * Prévisualiser les différentes modifications
     */

    public function previsualiserPassage(float $seuil = 9.50): array
    {
        $anneeActuelle = $this->anneeRepo->findOneBy(['actif' => true]);
        if (!$anneeActuelle) {
            throw new \RuntimeException('Aucune année scolaire active trouvée.');
        }

        $previsualisation = [];

        // Récupération des classes de l’année actuelle
        $classesActuelles = $this->classeRepo->findBy(['annee_scolaire' => $anneeActuelle]);

        foreach ($classesActuelles as $classe) {
            $inscriptions = $this->inscriptionRepo->findBy([
                'classe' => $classe,
                'AnneeScolaire' => $anneeActuelle,
                'actif' => true,
            ]);

            if (empty($inscriptions)) continue;

            $eleves = array_map(fn($i) => $i->getEleve(), $inscriptions);
            $moyennes = $this->bulletinManager->calculateAnnuelle($classe, $eleves);

            $elevesData = [];

            foreach ($inscriptions as $inscription) {
                $eleve = $inscription->getEleve();
                $moyenne = $moyennes[$eleve->getId()] ?? 0;
                $decision = $this->bulletinManager->getDecisionPassage($moyenne, $seuil);

                // Déterminer la future classe
                $nextClasse = $inscription->getClasse()->getNextClasse();
                $nomClasseSuivante = $nextClasse ? $nextClasse->getNom() : '—';

                if ($decision === 'redouble') {
                    $nomClasseSuivante = $classe->getNom() . ' (redouble)';
                }

                $elevesData[] = [
                    'eleve' => $eleve,
                    'moyenne' => $moyenne,
                    'decision' => $decision,
                    'classe_suivante' => $nomClasseSuivante,
                ];
            }

            $previsualisation[] = [
                'classe' => $classe,
                'eleves' => $elevesData,
            ];
        }

        return $previsualisation;
    }


    /**
     * Génère automatiquement le libellé de la prochaine année
     */
    private function genererNomNouvelleAnnee(string $nomActuel): string
    {
        if (preg_match('/(\d{4})[^\d]+(\d{4})/', $nomActuel, $m)) {
            $y1 = (int)$m[1] + 1;
            $y2 = (int)$m[2] + 1;
            return sprintf('%d-%d', $y1, $y2);
        }
        return date('Y') . '-' . (date('Y') + 1);
    }
}
