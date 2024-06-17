<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Detailscommande
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private int $id;

    #[ORM\Column(type: "integer")]
    private int $numArticle;

    #[ORM\Column(type: "string", length: 255)]
    private string $nomArticle;

    #[ORM\Column(type: "integer")]
    private int $quantite;

    #[ORM\Column(type: "float", precision: 10, scale: 0)]
    private float $prixUnitaire;

    #[ORM\Column(type: "float", precision: 10, scale: 0)]
    private float $sousTotal;

    #[ORM\ManyToOne(targetEntity: Commande::class)]
    #[ORM\JoinColumn(name: "id_com", referencedColumnName: "id")]
    private ?Commande $idCom;

    #[ORM\ManyToOne(targetEntity: Commande::class)]
    #[ORM\JoinColumn(name: "commande_id", referencedColumnName: "id")]
    private ?Commande $commande;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumArticle(): ?int
    {
        return $this->numArticle;
    }

    public function setNumArticle(int $numArticle): self
    {
        $this->numArticle = $numArticle;
        return $this;
    }

    public function getNomArticle(): ?string
    {
        return $this->nomArticle;
    }

    public function setNomArticle(string $nomArticle): self
    {
        $this->nomArticle = $nomArticle;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getPrixUnitaire(): ?float
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(float $prixUnitaire): self
    {
        $this->prixUnitaire = $prixUnitaire;
        return $this;
    }

    public function getSousTotal(): ?float
    {
        return $this->sousTotal;
    }

    public function setSousTotal(float $sousTotal): self
    {
        $this->sousTotal = $sousTotal;
        return $this;
    }

    public function getIdCom(): ?Commande
    {
        return $this->idCom;
    }

    public function setIdCom(?Commande $idCom): self
    {
        $this->idCom = $idCom;
        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;
        return $this;
    }
}
