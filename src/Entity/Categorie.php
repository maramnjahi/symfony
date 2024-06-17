<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\CategorieRepository;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'id_c')]
    private ?int $idC = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de la catégorie est obligatoire.")]
    private ?string $nomC = null;

    // Méthodes d'accès aux propriétés
    public function getIdC(): ?int
    {
        return $this->idC;
    }

    public function getNomC(): ?string
    {
        return $this->nomC;
    }

    public function setNomC(?string $nomC): self
    {
        $this->nomC = $nomC;
        return $this;
    }

    // Méthode spéciale __toString() pour convertir l'objet en chaîne
    public function __toString(): string
    {
        return $this->nomC ?? '';
    }
}