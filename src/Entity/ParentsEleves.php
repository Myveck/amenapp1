<?php

namespace App\Entity;

use App\Repository\ParentsElevesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParentsElevesRepository::class)]
class ParentsEleves
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'parentsEleves')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Eleves $eleve = null;

    #[ORM\ManyToOne(inversedBy: 'parentsEleves')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Parents $parent = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getParent(): ?Parents
    {
        return $this->parent;
    }

    public function setParent(?Parents $parent): static
    {
        $this->parent = $parent;

        return $this;
    }
}
