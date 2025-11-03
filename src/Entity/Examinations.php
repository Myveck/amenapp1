<?php

namespace App\Entity;

use App\Repository\ExaminationsRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExaminationsRepository::class)]
class Examinations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'examinations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Classes $classe = null;


    /**
     * @var Collection<int, Notes>
     */
    #[ORM\OneToMany(targetEntity: Notes::class, mappedBy: 'examinations', orphanRemoval: true)]
    private Collection $note;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_examination = null;

    #[ORM\ManyToOne(inversedBy: 'examinations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Matieres $matiere = null;

    #[ORM\Column]
    private ?int $trimestre = null;

    #[ORM\ManyToOne(inversedBy: 'examinations')]
    private ?AnneeScolaire $annee_scolaire = null;

    public function __construct()
    {
        $this->note = new ArrayCollection();
        $this->date_examination = new \DateTimeImmutable('now');
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

    /**
     * @return Collection<int, Notes>
     */
    public function getNote(): Collection
    {
        return $this->note;
    }

    public function addNote(Notes $note): static
    {
        if (!$this->note->contains($note)) {
            $this->note->add($note);
            $note->setExaminations($this);
        }

        return $this;
    }

    public function removeNote(Notes $note): static
    {
        if ($this->note->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getExaminations() === $this) {
                $note->setExaminations(null);
            }
        }

        return $this;
    }

    public function getDateExamination(): ?\DateTimeInterface
    {
        return $this->date_examination;
    }

    public function setDateExamination(\DateTimeInterface $date_examination): static
    {
        $this->date_examination = $date_examination;

        return $this;
    }

    public function getMatiere(): ?Matieres
    {
        return $this->matiere;
    }

    public function setMatiere(?Matieres $matiere): static
    {
        $this->matiere = $matiere;

        return $this;
    }

    public function getTrimestre(): ?int
    {
        return $this->trimestre;
    }

    public function setTrimestre(int $trimestre): static
    {
        $this->trimestre = $trimestre;

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
}
