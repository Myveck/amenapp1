<?php

namespace App\Entity;

use App\Repository\NotesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotesRepository::class)]
class Notes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $note = null;

    #[ORM\ManyToOne(inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Eleves $eleve = null;

    #[ORM\ManyToOne(inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Matieres $matiere = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_evaluation = null;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?int $Trimestre = null;

    #[ORM\ManyToOne(inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Evaluations $evaluation = null;

    #[ORM\ManyToOne(inversedBy: 'note')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Examinations $examinations = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable("now");
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNote(): ?float
    {
        return $this->note;
    }

    public function setNote(int|float $note): static
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

    public function getDateEvaluation(): ?\DateTime
    {
        return $this->date_evaluation;
    }

    public function setDateEvaluation(\DateTime $date_evaluation): static
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

    public function getTrimestre(): ?int
    {
        return $this->Trimestre;
    }

    public function setTrimestre(int $Trimestre): static
    {
        $this->Trimestre = $Trimestre;

        return $this;
    }

    public function getEvaluation(): ?Evaluations
    {
        return $this->evaluation;
    }

    public function setEvaluation(?Evaluations $evaluation): static
    {
        $this->evaluation = $evaluation;

        return $this;
    }

    public function getExaminations(): ?Examinations
    {
        return $this->examinations;
    }

    public function setExaminations(?Examinations $examinations): static
    {
        $this->examinations = $examinations;

        return $this;
    }
}
