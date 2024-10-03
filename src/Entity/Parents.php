<?php

namespace App\Entity;

use App\Repository\ParentsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParentsRepository::class)]
class Parents
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $profession = null;

    #[ORM\Column(length: 255)]
    private ?string $telephone = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 5)]
    private ?string $type = null;

    /**
     * @var Collection<int, ParentsEleves>
     */
    #[ORM\OneToMany(targetEntity: ParentsEleves::class, mappedBy: 'parent')]
    private Collection $parentsEleves;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable("now");
        $this->parentsEleves = new ArrayCollection();
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

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(string $profession): static
    {
        $this->profession = $profession;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, ParentsEleves>
     */
    public function getParentsEleves(): Collection
    {
        return $this->parentsEleves;
    }

    public function addParentsElefe(ParentsEleves $parentsElefe): static
    {
        if (!$this->parentsEleves->contains($parentsElefe)) {
            $this->parentsEleves->add($parentsElefe);
            $parentsElefe->setParent($this);
        }

        return $this;
    }

    public function removeParentsElefe(ParentsEleves $parentsElefe): static
    {
        if ($this->parentsEleves->removeElement($parentsElefe)) {
            // set the owning side to null (unless already changed)
            if ($parentsElefe->getParent() === $this) {
                $parentsElefe->setParent(null);
            }
        }

        return $this;
    }
}
