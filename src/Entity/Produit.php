<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ProduitRepository;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer',name:"id_P")]
    private ?int $idP = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du produit est obligatoire.")]
    private ?string $nomP = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description du produit est obligatoire.")]
    private ?string $descriptionP = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull(message: "Le prix du produit est obligatoire.")]
    #[Assert\Positive(message: "Le prix du produit doit être positif.")]
    private ?float $prixP = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'image du produit est obligatoire.")]
    private ?string $imageP = null;

    #[ORM\ManyToOne(targetEntity: Categorie::class, inversedBy: 'produits')]
    #[ORM\JoinColumn(name: 'id_c', referencedColumnName: 'id_c', nullable: false)]
    #[Assert\NotNull(message: "La catégorie est obligatoire.")]
    private ?Categorie $idC = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'produits')]
    #[ORM\JoinColumn(name: 'id_client', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull(message: "Le client est obligatoire.")]
    private ?User $idClient = null;
    // Méthodes d'accès aux propriétés
    public function getIdP(): ?int
    {
        return $this->idP;
    }

    public function getNomP(): ?string
    {
        return $this->nomP;
    }

    public function setNomP(?string $nomP): self
    {
        $this->nomP = $nomP;
        return $this;
    }

    public function getDescriptionP(): ?string
    {
        return $this->descriptionP;
    }

    public function setDescriptionP(?string $descriptionP): self
    {
        $this->descriptionP = $descriptionP;
        return $this;
    }

    public function getPrixP(): ?float
    {
        return $this->prixP;
    }

    public function setPrixP(?float $prixP): self
    {
        $this->prixP = $prixP;
        return $this;
    }

    public function getImageP(): ?string
    {
        return $this->imageP;
    }

    public function setImageP(?string $imageP): self
    {
        $this->imageP = $imageP;
        return $this;
    }

    public function getIdClient(): ?User
    {
        return $this->idClient;
    }

    public function setIdClient(?User $idClient): self
    {
        $this->idClient = $idClient;
        return $this;
    }

    public function getIdC(): ?Categorie
    {
        return $this->idC;
    }

    public function setIdC(?Categorie $idC): self
    {
        $this->idC = $idC;
        return $this;
    }
}