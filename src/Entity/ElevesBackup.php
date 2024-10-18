<?php

namespace App\Entity;

use App\Repository\ElevesBackupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ElevesBackupRepository::class)]
class ElevesBackup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $classe = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, PaiementsBackup>
     */
    #[ORM\OneToMany(targetEntity: PaiementsBackup::class, mappedBy: 'eleveBackup', orphanRemoval: true)]
    private Collection $paiementsBackups;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $anneeScolaire = null;

    public function __construct()
    {
        $this->paiementsBackups = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection<int, PaiementsBackup>
     */
    public function getPaiementsBackups(): Collection
    {
        return $this->paiementsBackups;
    }

    public function addPaiementsBackup(PaiementsBackup $paiementsBackup): static
    {
        if (!$this->paiementsBackups->contains($paiementsBackup)) {
            $this->paiementsBackups->add($paiementsBackup);
            $paiementsBackup->setEleveBackup($this);
        }

        return $this;
    }

    public function removePaiementsBackup(PaiementsBackup $paiementsBackup): static
    {
        if ($this->paiementsBackups->removeElement($paiementsBackup)) {
            // set the owning side to null (unless already changed)
            if ($paiementsBackup->getEleveBackup() === $this) {
                $paiementsBackup->setEleveBackup(null);
            }
        }

        return $this;
    }

    public function getAnneeScolaire(): ?string
    {
        return $this->anneeScolaire;
    }

    public function setAnneeScolaire(?string $anneeScolaire): static
    {
        $this->anneeScolaire = $anneeScolaire;

        return $this;
    }
}
