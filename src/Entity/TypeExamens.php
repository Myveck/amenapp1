<?php

namespace App\Entity;

use App\Repository\TypeExamensRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeExamensRepository::class)]
class TypeExamens
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?int $type_order = null;

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

    public function getTypeOrder(): ?int
    {
        return $this->type_order;
    }

    public function setTypeOrder(int $type_order): static
    {
        $this->type_order = $type_order;

        return $this;
    }
}
