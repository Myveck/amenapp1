<?php

namespace App\Entity;

use App\Repository\EmploisDuTempsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmploisDuTempsRepository::class)]
class EmploisDuTemps
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'emploisDuTemps')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Classes $classe = null;

    /**
     * @var Collection<int, Matieres>
     */
    #[ORM\ManyToMany(targetEntity: Matieres::class, inversedBy: 'emploisDuTemps')]
    private Collection $matiere;

    #[ORM\Column(length: 255)]
    private ?string $jour = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $heure_debut = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $heure_fin = null;

    public function __construct()
    {
        $this->matiere = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClasseId(): ?Classes
    {
        return $this->classe;
    }

    public function setClasseId(?Classes $classe_id): static
    {
        $this->classe = $classe_id;

        return $this;
    }

    /**
     * @return Collection<int, Matieres>
     */
    public function getMatiereId(): Collection
    {
        return $this->matiere;
    }

    public function addMatiereId(Matieres $matiereId): static
    {
        if (!$this->matiere->contains($matiereId)) {
            $this->matiere->add($matiereId);
        }

        return $this;
    }

    public function removeMatiereId(Matieres $matiereId): static
    {
        $this->matiere->removeElement($matiereId);

        return $this;
    }

    public function getJour(): ?string
    {
        return $this->jour;
    }

    public function setJour(string $jour): static
    {
        $this->jour = $jour;

        return $this;
    }

    public function getHeureDebut(): ?\DateTimeImmutable
    {
        return $this->heure_debut;
    }

    public function setHeureDebut(\DateTimeImmutable $heure_debut): static
    {
        $this->heure_debut = $heure_debut;

        return $this;
    }

    public function getHeureFin(): ?\DateTimeImmutable
    {
        return $this->heure_fin;
    }

    public function setHeureFin(\DateTimeImmutable $heure_fin): static
    {
        $this->heure_fin = $heure_fin;

        return $this;
    }
}
