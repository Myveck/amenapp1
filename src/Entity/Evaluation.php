<?php

namespace App\Entity;

use App\Repository\EvaluationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'evaluations')]
    private ?Examinations $examination = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExamination(): ?Examinations
    {
        return $this->examination;
    }

    public function setExamination(?Examinations $examination): static
    {
        $this->examination = $examination;

        return $this;
    }
}
