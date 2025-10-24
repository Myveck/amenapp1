<?php

namespace App\Entity;

use App\Repository\ClassesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClassesRepository::class)]
class Classes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $niveau = null;

    #[ORM\Column]
    private ?int $classeOrder = null;

    /**
     * @var Collection<int, ClassesMatieres>
     */
    #[ORM\OneToMany(targetEntity: ClassesMatieres::class, mappedBy: 'classe', orphanRemoval: true)]
    private Collection $classesMatieres;

    #[ORM\ManyToOne(inversedBy: 'classes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AnneeScolaire $annee_scolaire = null;
    
    #[ORM\ManyToOne(inversedBy: 'classes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Eleves $eleve = null;

    /**
     * @var Collection<int, Examinations>
     */
    #[ORM\OneToMany(targetEntity: Examinations::class, mappedBy: 'classe', orphanRemoval: true)]
    private Collection $examinations;

    /**
     * @var Collection<int, Inscription>
     */
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'classe')]
    private Collection $inscriptions;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'classes')]
    private ?self $next_classe = null;

    

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'next_classe')]
    private Collection $classes;

    public function __construct()
    {
        $this->classesMatieres = new ArrayCollection();
        $this->examinations = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
        $this->classes = new ArrayCollection();
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

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getClasseOrder(): ?string
    {
        return $this->classeOrder;
    }

    public function setClasseOrder(string $nom): static
    {
        $this->classeOrder = $nom;

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
            $classesMatiere->setClasse($this);
        }

        return $this;
    }

    public function removeClassesMatiere(ClassesMatieres $classesMatiere): static
    {
        if ($this->classesMatieres->removeElement($classesMatiere)) {
            // set the owning side to null (unless already changed)
            if ($classesMatiere->getClasse() === $this) {
                $classesMatiere->setClasse(null);
            }
        }

        return $this;
    }

    public function getAnneeScolaire(): ?AnneeScolaire
    {
        return $this->annee_scolaire;
    }

    public function setAnneeScolaire(?AnneeScolaire $anne_scolaire): static
    {
        $this->annee_scolaire = $anne_scolaire;

        return $this;
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
            $examination->setClasse($this);
        }

        return $this;
    }

    public function removeExamination(Examinations $examination): static
    {
        if ($this->examinations->removeElement($examination)) {
            // set the owning side to null (unless already changed)
            if ($examination->getClasse() === $this) {
                $examination->setClasse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Inscription>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): static
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setClasse($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            // set the owning side to null (unless already changed)
            if ($inscription->getClasse() === $this) {
                $inscription->setClasse(null);
            }
        }

        return $this;
    }

    public function getNextClasse(): ?self
    {
        return $this->next_classe;
    }

    public function setNextClasse(?self $next_classe): static
    {
        $this->next_classe = $next_classe;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getClasses(): Collection
    {
        return $this->classes;
    }

    public function addClass(self $class): static
    {
        if (!$this->classes->contains($class)) {
            $this->classes->add($class);
            $class->setNextClasse($this);
        }

        return $this;
    }

    public function removeClass(self $class): static
    {
        if ($this->classes->removeElement($class)) {
            // set the owning side to null (unless already changed)
            if ($class->getNextClasse() === $this) {
                $class->setNextClasse(null);
            }
        }

        return $this;
    }
}
