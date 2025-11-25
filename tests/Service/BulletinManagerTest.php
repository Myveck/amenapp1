<?php

namespace App\Tests\Service;

use App\Entity\Eleves;
use App\Entity\Matiere;
use App\Entity\Notes;
use App\Service\BulletinManager2;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class BulletinManagerTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private BulletinManager2 $bulletinManager2;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->bulletinManager2 = $container->get(BulletinManager2::class);
    }

    public function testCalculBulletin()
    {
        // --- Création d’un élève factice ---
        $eleve = new Eleves();
        $eleve->setNom('Élève Test');
        $this->em->persist($eleve);

        // --- Création de deux matières ---
        $maths = (new Matiere())->setNom('Maths');
        $physique = (new Matiere())->setNom('Physique');
        $this->em->persist($maths);
        $this->em->persist($physique);

        // --- Ajout de notes ---
        $note1 = (new Notes())->setEleve($eleve)->setMatiere($maths)->setValeur(14);
        $note2 = (new Notes())->setEleve($eleve)->setMatiere($physique)->setValeur(10);
        $this->em->persist($note1);
        $this->em->persist($note2);

        $this->em->flush();

        // --- Appel du service ---
        $resultat = $this->bulletinManager->calculerBulletin($eleve);

        // --- Vérifications ---
        $this->assertIsArray($resultat, 'Le résultat doit être un tableau.');
        $this->assertArrayHasKey('moyenne', $resultat, 'Le bulletin doit contenir une moyenne.');
        $this->assertEquals(12, round($resultat['moyenne']), 'La moyenne devrait être correcte.');

        // --- Nettoyage (optionnel) ---
        $this->em->remove($note1);
        $this->em->remove($note2);
        $this->em->remove($maths);
        $this->em->remove($physique);
        $this->em->remove($eleve);
        $this->em->flush();
    }
}
