<?php

namespace App\Entity;

use App\Repository\EcolesRepository;
use App\Repository\TarifRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TarifRepository::class)]
class Tarif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Classes $classe = null;

    #[ORM\Column]
    private ?int $prix_annuel = null;

    #[ORM\Column]
    private ?int $prix_inscription = null;

    #[ORM\ManyToOne(inversedBy: 'tarifs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AnneeScolaire $annee_scolaire = null;

    #[ORM\Column]
    private ?int $prix_reinscription = null;

    public function __construct(AnneeScolaire $annee_scolaire)
    {
        $this->annee_scolaire = $annee_scolaire;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrixAnnuel(): ?int
    {
        return $this->prix_annuel;
    }

    public function setPrixAnnuel(int $prix_annuel): static
    {
        $this->prix_annuel = $prix_annuel;

        return $this;
    }

    public function getPrixInscription(): ?int
    {
        return $this->prix_inscription;
    }

    public function setPrixInscription(int $prix_inscription): static
    {
        $this->prix_inscription = $prix_inscription;

        return $this;
    }

    public function getAnneeScolaire(): ?AnneeScolaire
    {
        return $this->annee_scolaire;
    }

    public function setAnneeScolaire(?AnneeScolaire $annee_scolaire): static
    {
        $this->annee_scolaire = $annee_scolaire;

        return $this;
    }

    public function getPrixReinscription(): ?int
    {
        return $this->prix_reinscription;
    }

    public function setPrixReinscription(int $prix_reinscription): static
    {
        $this->prix_reinscription = $prix_reinscription;

        return $this;
    }
}
