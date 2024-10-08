<?php

namespace App\Entity;

use App\Repository\MatieresRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatieresRepository::class)]
class Matieres
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Notes>
     */
    #[ORM\OneToMany(targetEntity: Notes::class, mappedBy: 'matiere_id', orphanRemoval: true)]
    private Collection $notes;

    /**
     * @var Collection<int, Enseignants>
     */
    #[ORM\OneToMany(targetEntity: Enseignants::class, mappedBy: 'matiere_id')]
    private Collection $enseignants;

    /**
     * @var Collection<int, EmploisDuTemps>
     */
    #[ORM\ManyToMany(targetEntity: EmploisDuTemps::class, mappedBy: 'matiere_id')]
    private Collection $emploisDuTemps;

    /**
     * @var Collection<int, ClassesMatieres>
     */
    #[ORM\OneToMany(targetEntity: ClassesMatieres::class, mappedBy: 'matiere', orphanRemoval: true)]
    private Collection $classesMatieres;

    /**
     * @var Collection<int, Examinations>
     */
    #[ORM\OneToMany(targetEntity: Examinations::class, mappedBy: 'matiere', orphanRemoval: true)]
    private Collection $examinations;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
        $this->enseignants = new ArrayCollection();
        $this->emploisDuTemps = new ArrayCollection();
        $this->classesMatieres = new ArrayCollection();
        $this->examinations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Notes>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Notes $note): static
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setMatiereId($this);
        }

        return $this;
    }

    public function removeNote(Notes $note): static
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getMatiereId() === $this) {
                $note->setMatiereId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Enseignants>
     */
    public function getEnseignants(): Collection
    {
        return $this->enseignants;
    }

    public function addEnseignant(Enseignants $enseignant): static
    {
        if (!$this->enseignants->contains($enseignant)) {
            $this->enseignants->add($enseignant);
            $enseignant->setMatiereId($this);
        }

        return $this;
    }

    public function removeEnseignant(Enseignants $enseignant): static
    {
        if ($this->enseignants->removeElement($enseignant)) {
            // set the owning side to null (unless already changed)
            if ($enseignant->getMatiereId() === $this) {
                $enseignant->setMatiereId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EmploisDuTemps>
     */
    public function getEmploisDuTemps(): Collection
    {
        return $this->emploisDuTemps;
    }

    public function addEmploisDuTemp(EmploisDuTemps $emploisDuTemp): static
    {
        if (!$this->emploisDuTemps->contains($emploisDuTemp)) {
            $this->emploisDuTemps->add($emploisDuTemp);
            $emploisDuTemp->addMatiereId($this);
        }

        return $this;
    }

    public function removeEmploisDuTemp(EmploisDuTemps $emploisDuTemp): static
    {
        if ($this->emploisDuTemps->removeElement($emploisDuTemp)) {
            $emploisDuTemp->removeMatiereId($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, ClassesMatieres>
     */
    public function getClassesMatieres(): Collection
    {
        return $this->classesMatieres;
    }

    public function addClassesMatiere(ClassesMatieres $classesMatiere): static
    {
        if (!$this->classesMatieres->contains($classesMatiere)) {
            $this->classesMatieres->add($classesMatiere);
            $classesMatiere->setMatiere($this);
        }

        return $this;
    }

    public function removeClassesMatiere(ClassesMatieres $classesMatiere): static
    {
        if ($this->classesMatieres->removeElement($classesMatiere)) {
            // set the owning side to null (unless already changed)
            if ($classesMatiere->getMatiere() === $this) {
                $classesMatiere->setMatiere(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Examinations>
     */
    public function getExaminations(): Collection
    {
        return $this->examinations;
    }

    public function addExamination(Examinations $examination): static
    {
        if (!$this->examinations->contains($examination)) {
            $this->examinations->add($examination);
            $examination->setMatiere($this);
        }

        return $this;
    }

    public function removeExamination(Examinations $examination): static
    {
        if ($this->examinations->removeElement($examination)) {
            // set the owning side to null (unless already changed)
            if ($examination->getMatiere() === $this) {
                $examination->setMatiere(null);
            }
        }

        return $this;
    }
}
