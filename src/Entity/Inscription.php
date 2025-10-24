<?php

namespace App\Entity;

use App\Repository\InscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions', cascade: ['persist'])]
    private ?Eleves $eleve = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    private ?Classes $classe = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    private ?AnneeScolaire $AnneeScolaire = null;

    #[ORM\Column]
    private ?bool $redouble = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInscription = null;

    #[ORM\Column(nullable: true)]
    private ?float $moyenne_annuelle = null;

      public function __construct()
    {
        $this->dateInscription = new \DateTimeImmutable("now");
        $this->redouble = false;
        $this->actif = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEleve(): ?Eleves
    {
        return $this->eleve;
    }

    public function setEleve(?Eleves $eleve): static
    {
        $this->eleve = $eleve;

        return $this;
    }

    public function getClasse(): ?Classes
    {
        return $this->classe;
    }

    public function setClasse(?Classes $classe): static
    {
        $this->classe = $classe;

        return $this;
    }

    public function getAnneeScolaire(): ?AnneeScolaire
    {
        return $this->AnneeScolaire;
    }

    public function setAnneeScolaire(?AnneeScolaire $AnneeScolaire): static
    {
        $this->AnneeScolaire = $AnneeScolaire;

        return $this;
    }

    public function isRedouble(): ?bool
    {
        return $this->redouble;
    }

    public function setRedouble(bool $redouble): static
    {
        $this->redouble = $redouble;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): static
    {
        $this->dateInscription = $dateInscription;

        return $this;
    }

    public function getMoyenneAnnuelle(): ?float
    {
        return $this->moyenne_annuelle;
    }

    public function setMoyenneAnnuelle(?float $moyenne_annuelle): static
    {
        $this->moyenne_annuelle = $moyenne_annuelle;

        return $this;
    }
}
