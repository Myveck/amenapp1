<?php

namespace App\Command;

use App\Entity\Inscription;
use App\Repository\ElevesRepository;
use App\Repository\ClassesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:migrate-inscriptions',
    description: 'Crée les inscriptions pour tous les élèves existants à partir de leur classe et année actuelle.'
)]
class MigrateInscriptionsCommand extends Command
{
    private ElevesRepository $elevesRepository;
    private EntityManagerInterface $em;

    public function __construct(ElevesRepository $elevesRepository, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->elevesRepository = $elevesRepository;
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $eleves = $this->elevesRepository->findAll();

        if (empty($eleves)) {
            $output->writeln('<comment>Aucun élève trouvé dans la base.</comment>');
            return Command::SUCCESS;
        }

        $count = 0;

        foreach ($eleves as $eleve) {
            $classe = $eleve->getClasse();
            if (!$classe) {
                $output->writeln("<fg=yellow>⚠ Élève sans classe ignoré : {$eleve->getNom()}</>");
                continue;
            }

            $annee = $classe->getAnneeScolaire();
            if (!$annee) {
                $output->writeln("<fg=yellow>⚠ Classe sans année scolaire ignorée : {$classe->getNom()}</>");
                continue;
            }

            $inscription = new Inscription();
            $inscription
                ->setEleve($eleve)
                ->setClasse($classe)
                ->setAnneeScolaire($annee)
                ->setActif(true)
                ->setRedouble(false)
                ->setDateInscription(new \DateTime());

            $this->em->persist($inscription);
            $count++;
        }

        $this->em->flush();

        $output->writeln("<info>✅ {$count} inscriptions créées avec succès.</info>");
        return Command::SUCCESS;
    }
}
