<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CommandeRepository;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $etat;

    #[ORM\Column(type: "integer")]
    private int $cmdClient;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private \DateTimeInterface $cmdDate;

    #[ORM\ManyToOne(targetEntity: Panier::class)]
    #[ORM\JoinColumn(name: "panier_id", referencedColumnName: "id")]
    private ?Panier $panier;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id")]
    private ?User $user;

    public function __construct()
    {
        $this->cmdDate = new \DateTime();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;
        return $this;
    }

    public function getCmdClient(): ?int
    {
        return $this->cmdClient;
    }

    public function setCmdClient(int $cmdClient): self
    {
        $this->cmdClient = $cmdClient;
        return $this;
    }

    public function getCmdDate(): ?\DateTimeInterface
    {
        return $this->cmdDate;
    }

    public function setCmdDate(\DateTimeInterface $cmdDate): self
    {
        $this->cmdDate = $cmdDate;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
