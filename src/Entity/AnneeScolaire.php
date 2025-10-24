<?php

namespace App\Entity;

use App\Repository\AnneeScolaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnneeScolaireRepository::class)]
class AnneeScolaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 11)]
    private ?string $annee = null;

    /**
     * @var Collection<int, Classes>
     */
    #[ORM\OneToMany(targetEntity: Classes::class, mappedBy: 'annee_scolaire')]
    private Collection $classes;

    /**
     * @var Collection<int, Paiements>
     */
    #[ORM\OneToMany(targetEntity: Paiements::class, mappedBy: 'annee_scolaire')]
    private Collection $paiements;

    /**
     * @var Collection<int, ClassesMatieres>
     */
    #[ORM\OneToMany(targetEntity: ClassesMatieres::class, mappedBy: 'annee_scolaire')]
    private Collection $classesMatieres;

    /**
     * @var Collection<int, Tarif>
     */
    #[ORM\OneToMany(targetEntity: Tarif::class, mappedBy: 'annee_scolaire')]
    private Collection $tarifs;

    /**
     * @var Collection<int, Evaluations>
     */
    #[ORM\OneToMany(targetEntity: Evaluations::class, mappedBy: 'annee_scolaire')]
    private Collection $evaluations;

    /**
     * @var Collection<int, PaiementsBackup>
     */
    #[ORM\OneToMany(targetEntity: PaiementsBackup::class, mappedBy: 'annee_scolaire', orphanRemoval: true)]
    private Collection $paiementsBackups;

    /**
     * @var Collection<int, Ecoles>
     */
    #[ORM\OneToMany(targetEntity: Ecoles::class, mappedBy: 'anneeScolaire', orphanRemoval: true)]
    private Collection $ecoles;

    /**
     * @var Collection<int, ClassesBackup>
     */
    #[ORM\OneToMany(targetEntity: ClassesBackup::class, mappedBy: 'anneeScolaire', orphanRemoval: true)]
    private Collection $classesBackups;

    /**
     * @var Collection<int, TarifBackup>
     */
    #[ORM\OneToMany(targetEntity: TarifBackup::class, mappedBy: 'AnneeScolaire')]
    private Collection $tarifBackups;

    /**
     * @var Collection<int, Eleves>
     */
    #[ORM\OneToMany(targetEntity: Eleves::class, mappedBy: 'annee_scolaire')]
    private Collection $eleves;

    /**
     * @var Collection<int, Inscription>
     */
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'AnneeScolaire')]
    private Collection $inscriptions;

    #[ORM\Column(nullable: true)]
    private ?bool $actif = null;


    public function __construct()
    {
        $this->classes = new ArrayCollection();
        $this->paiements = new ArrayCollection();
        $this->classesMatieres = new ArrayCollection();
        $this->tarifs = new ArrayCollection();
        $this->evaluations = new ArrayCollection();
        $this->paiementsBackups = new ArrayCollection();
        $this->ecoles = new ArrayCollection();
        $this->classesBackups = new ArrayCollection();
        $this->tarifBackups = new ArrayCollection();
        $this->eleves = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnnee(): ?string
    {
        return $this->annee;
    }

    public function setAnnee(string $annee): static
    {
        $this->annee = $annee;

        return $this;
    }



    /**
     * @return Collection<int, Paiements>
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiements $paiement): static
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements->add($paiement);
            $paiement->setAnneeScolaire($this);
        }

        return $this;
    }

    public function removePaiement(Paiements $paiement): static
    {
        if ($this->paiements->removeElement($paiement)) {
            // set the owning side to null (unless already changed)
            if ($paiement->getAnneeScolaire() === $this) {
                $paiement->setAnneeScolaire(null);
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
            $classesMatiere->setAnneeScolaire($this);
        }

        return $this;
    }

    public function removeClassesMatiere(ClassesMatieres $classesMatiere): static
    {
        if ($this->classesMatieres->removeElement($classesMatiere)) {
            // set the owning side to null (unless already changed)
            if ($classesMatiere->getAnneeScolaire() === $this) {
                $classesMatiere->setAnneeScolaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tarif>
     */
    public function getTarifs(): Collection
    {
        return $this->tarifs;
    }

    public function addTarif(Tarif $tarif): static
    {
        if (!$this->tarifs->contains($tarif)) {
            $this->tarifs->add($tarif);
            $tarif->setAnneeScolaire($this);
        }

        return $this;
    }

    public function removeTarif(Tarif $tarif): static
    {
        if ($this->tarifs->removeElement($tarif)) {
            // set the owning side to null (unless already changed)
            if ($tarif->getAnneeScolaire() === $this) {
                $tarif->setAnneeScolaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Evaluations>
     */
    public function getEvaluations(): Collection
    {
        return $this->evaluations;
    }

    public function addEvaluation(Evaluations $evaluation): static
    {
        if (!$this->evaluations->contains($evaluation)) {
            $this->evaluations->add($evaluation);
            $evaluation->setAnneeScolaire($this);
        }

        return $this;
    }

    public function removeEvaluation(Evaluations $evaluation): static
    {
        if ($this->evaluations->removeElement($evaluation)) {
            // set the owning side to null (unless already changed)
            if ($evaluation->getAnneeScolaire() === $this) {
                $evaluation->setAnneeScolaire(null);
            }
        }

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
            $paiementsBackup->setAnneeScolaire($this);
        }

        return $this;
    }

    public function removePaiementsBackup(PaiementsBackup $paiementsBackup): static
    {
        if ($this->paiementsBackups->removeElement($paiementsBackup)) {
            // set the owning side to null (unless already changed)
            if ($paiementsBackup->getAnneeScolaire() === $this) {
                $paiementsBackup->setAnneeScolaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ecoles>
     */
    public function getEcoles(): Collection
    {
        return $this->ecoles;
    }

    public function addEcole(Ecoles $ecole): static
    {
        if (!$this->ecoles->contains($ecole)) {
            $this->ecoles->add($ecole);
            $ecole->setAnneeScolaire($this);
        }

        return $this;
    }

    public function removeEcole(Ecoles $ecole): static
    {
        if ($this->ecoles->removeElement($ecole)) {
            // set the owning side to null (unless already changed)
            if ($ecole->getAnneeScolaire() === $this) {
                $ecole->setAnneeScolaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ClassesBackup>
     */
    public function getClassesBackups(): Collection
    {
        return $this->classesBackups;
    }

    public function addClassesBackup(ClassesBackup $classesBackup): static
    {
        if (!$this->classesBackups->contains($classesBackup)) {
            $this->classesBackups->add($classesBackup);
            $classesBackup->setAnneeScolaire($this);
        }

        return $this;
    }

    public function removeClassesBackup(ClassesBackup $classesBackup): static
    {
        if ($this->classesBackups->removeElement($classesBackup)) {
            // set the owning side to null (unless already changed)
            if ($classesBackup->getAnneeScolaire() === $this) {
                $classesBackup->setAnneeScolaire(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, TarifBackup>
     */
    public function getTarifBackups(): Collection
    {
        return $this->tarifBackups;
    }

    public function addTarifBackup(TarifBackup $tarifBackup): static
    {
        if (!$this->tarifBackups->contains($tarifBackup)) {
            $this->tarifBackups->add($tarifBackup);
            $tarifBackup->setAnneeScolaire($this);
        }

        return $this;
    }

    public function removeTarifBackup(TarifBackup $tarifBackup): static
    {
        if ($this->tarifBackups->removeElement($tarifBackup)) {
            // set the owning side to null (unless already changed)
            if ($tarifBackup->getAnneeScolaire() === $this) {
                $tarifBackup->setAnneeScolaire(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, Classes>
     */
    public function getClasses(): Collection
    {
        return $this->classes;
    }

    public function addClasses(Classes $classe): static
    {
        if (!$this->classes->contains($classe)) {
            $this->classes->add($classe);
            $classe->setAnneeScolaire($this);
        }

        return $this;
    }

    public function removeClasses(TarifBackup $classe): static
    {
        if ($this->tarifBackups->removeElement($classe)) {
            // set the owning side to null (unless already changed)
            if ($classe->getAnneeScolaire() === $this) {
                $classe->setAnneeScolaire(null);
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
            $elefe->setAnneeScolaire($this);
        }

        return $this;
    }

    public function removeElefe(Eleves $elefe): static
    {
        if ($this->eleves->removeElement($elefe)) {
            // set the owning side to null (unless already changed)
            if ($elefe->getAnneeScolaire() === $this) {
                $elefe->setAnneeScolaire(null);
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
            $inscription->setAnneeScolaire($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            // set the owning side to null (unless already changed)
            if ($inscription->getAnneeScolaire() === $this) {
                $inscription->setAnneeScolaire(null);
            }
        }

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }
}
