<?php

namespace App\Entity;

use App\Repository\NotesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotesRepository::class)]
class Notes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $note = null;

    #[ORM\ManyToOne(inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Eleves $eleve = null;

    #[ORM\ManyToOne(inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Matieres $matiere = null;

    #[ORM\Column(length: 255)]
    private ?string $type_evaluation = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date_evaluation = null;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $created_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable("now");
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getEleveId(): ?Eleves
    {
        return $this->eleve;
    }

    public function setEleveId(?Eleves $eleve_id): static
    {
        $this->eleve = $eleve_id;

        return $this;
    }

    public function getMatiereId(): ?Matieres
    {
        return $this->matiere;
    }

    public function setMatiereId(?Matieres $matiere_id): static
    {
        $this->matiere = $matiere_id;

        return $this;
    }

    public function getTypeEvaluation(): ?string
    {
        return $this->type_evaluation;
    }

    public function setTypeEvaluation(string $type_evaluation): static
    {
        $this->type_evaluation = $type_evaluation;

        return $this;
    }

    public function getDateEvaluation(): ?\DateTimeImmutable
    {
        return $this->date_evaluation;
    }

    public function setDateEvaluation(\DateTimeImmutable $date_evaluation): static
    {
        $this->date_evaluation = $date_evaluation;

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
}
