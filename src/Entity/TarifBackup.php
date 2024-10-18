<?php

namespace App\Entity;

use App\Repository\TarifBackupRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TarifBackupRepository::class)]
class TarifBackup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $classe = null;

    #[ORM\Column]
    private ?int $prixAnnuel = null;

    #[ORM\Column(length: 255)]
    private ?int $prixInscription = null;

    #[ORM\Column]
    private ?int $PrixReinscription = null;

    #[ORM\ManyToOne(inversedBy: 'tarifBackups')]
    private ?AnneeScolaire $AnneeScolaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClasse(): ?string
    {
        return $this->classe;
    }

    public function setClasse(string $classe): static
    {
        $this->classe = $classe;

        return $this;
    }

    public function getPrixAnnuel(): ?int
    {
        return $this->prixAnnuel;
    }

    public function setPrixAnnuel(int $prixAnnuel): static
    {
        $this->prixAnnuel = $prixAnnuel;

        return $this;
    }

    public function getPrixInscription(): ?int
    {
        return $this->prixInscription;
    }

    public function setPrixInscription(int $prixInscription): static
    {
        $this->prixInscription = $prixInscription;

        return $this;
    }

    public function getPrixReinscription(): ?int
    {
        return $this->PrixReinscription;
    }

    public function setPrixReinscription(int $PrixReinscription): static
    {
        $this->PrixReinscription = $PrixReinscription;

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
}
