<?php

namespace App\Entity;

use App\Repository\RechercheSortieRepository;
use Doctrine\ORM\Mapping as ORM;


class RechercheSortie
{
    private $id;

    private $nom;

    private $dateDebutRecherche;

    private $dateFinRecherche;

    private $isOrganisateur;

    private $isRegistered;

    private $isNotRegistered;

    private $isFinished;

    private $campus;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateDebutRecherche(): ?\DateTimeInterface
    {
        return $this->dateDebutRecherche;
    }

    public function setDateDebutRecherche(?\DateTimeInterface $dateDebutRecherche): self
    {
        $this->dateDebutRecherche = $dateDebutRecherche;

        return $this;
    }

    public function getDateFinRecherche(): ?\DateTimeInterface
    {
        return $this->dateFinRecherche;
    }

    public function setDateFinRecherche(?\DateTimeInterface $dateFinRecherche): self
    {
        $this->dateFinRecherche = $dateFinRecherche;

        return $this;
    }

    public function getIsOrganisateur(): ?bool
    {
        return $this->isOrganisateur;
    }

    public function setIsOrganisateur(?bool $isOrganisateur): self
    {
        $this->isOrganisateur = $isOrganisateur;

        return $this;
    }

    public function getIsRegistered(): ?bool
    {
        return $this->isRegistered;
    }

    public function setIsRegistered(?bool $isRegistered): self
    {
        $this->isRegistered = $isRegistered;

        return $this;
    }

    public function getIsNotRegistered(): ?bool
    {
        return $this->isNotRegistered;
    }

    public function setIsNotRegistered(?bool $isNotRegistered): self
    {
        $this->isNotRegistered = $isNotRegistered;

        return $this;
    }

    public function getIsFinished(): ?bool
    {
        return $this->isFinished;
    }

    public function setIsFinished(?bool $isFinished): self
    {
        $this->isFinished = $isFinished;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }
}
