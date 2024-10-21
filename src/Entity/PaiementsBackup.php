<?php

namespace App\Entity;

use App\Repository\PaiementsBackupRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementsBackupRepository::class)]
class PaiementsBackup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $montant = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'paiementsBackups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AnneeScolaire $annee_scolaire = null;

    #[ORM\ManyToOne(inversedBy: 'paiementsBackups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ElevesBackup $eleveBackup = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $maj = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable('now');
        $this->maj = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontant(): ?int
    {
        return $this->montant;
    }

    public function setMontant(int $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

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

    public function getEleveBackup(): ?ElevesBackup
    {
        return $this->eleveBackup;
    }

    public function setEleveBackup(?ElevesBackup $eleveBackup): static
    {
        $this->eleveBackup = $eleveBackup;

        return $this;
    }

    public function getMaj(): ?string
    {
        return $this->maj;
    }

    public function setMaj(?string $maj): static
    {
        $this->maj = $maj;

        return $this;
    }
}
