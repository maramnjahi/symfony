<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PanierProduitRepository;

#[ORM\Entity(repositoryClass: PanierProduitRepository::class)]

class PanierProduit
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private int $id;
    
    #[ORM\Column(type: "integer")]
    #[ORM\JoinColumn(name: "panier_id", referencedColumnName: "id")]
    private int $produitId;

    #[ORM\ManyToOne(targetEntity: Panier::class)]
    #[ORM\JoinColumn(name: "panier_id", referencedColumnName: "id")]
    private ?Panier $panier;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getProduitId(): int
    {
        return $this->produitId;
    }

    public function setProduitId(int $produitId): self
    {
        $this->produitId = $produitId;
        return $this;
    }

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(?Panier $panier): self
    {
        $this->panier = $panier;
        return $this;
    }

    // Additional methods can be added here as needed
}
