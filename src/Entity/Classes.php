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

    #[ORM\ManyToOne(inversedBy: 'classes')]
    private ?Series $serie = null;

    /**
     * @var Collection<int, EmploisDuTemps>
     */
    #[ORM\OneToMany(targetEntity: EmploisDuTemps::class, mappedBy: 'classe_id', orphanRemoval: true)]
    private Collection $emploisDuTemps;

    /**
     * @var Collection<int, ClassesMatieres>
     */
    #[ORM\OneToMany(targetEntity: ClassesMatieres::class, mappedBy: 'classe', orphanRemoval: true)]
    private Collection $classesMatieres;

    /**
     * @var Collection<int, Eleves>
     */
    #[ORM\OneToMany(targetEntity: Eleves::class, mappedBy: 'classe')]
    private Collection $eleves;

    public function __construct()
    {
        $this->emploisDuTemps = new ArrayCollection();
        $this->classesMatieres = new ArrayCollection();
        $this->eleves = new ArrayCollection();
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

    public function getOrder(): ?string
    {
        return $this->classeOrder;
    }

    public function setOrder(string $nom): static
    {
        $this->classeOrder = $nom;

        return $this;
    }

    public function getSerie(): ?Series
    {
        return $this->serie;
    }

    public function setSerie(?Series $serie): static
    {
        $this->serie = $serie;

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
            $emploisDuTemp->setClasseId($this);
        }

        return $this;
    }

    public function removeEmploisDuTemp(EmploisDuTemps $emploisDuTemp): static
    {
        if ($this->emploisDuTemps->removeElement($emploisDuTemp)) {
            // set the owning side to null (unless already changed)
            if ($emploisDuTemp->getClasseId() === $this) {
                $emploisDuTemp->setClasseId(null);
            }
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

    /**
     * @return Collection<int, Eleves>
     */
    public function getEleves(): Collection
    {
        return $this->eleves;
    }

    public function addElefe(Eleves $elefe): static
    {
        if (!$this->eleves->contains($elefe)) {
            $this->eleves->add($elefe);
            $elefe->setClasse($this);
        }

        return $this;
    }

    public function removeElefe(Eleves $elefe): static
    {
        if ($this->eleves->removeElement($elefe)) {
            // set the owning side to null (unless already changed)
            if ($elefe->getClasse() === $this) {
                $elefe->setClasse(null);
            }
        }

        return $this;
    }
}
