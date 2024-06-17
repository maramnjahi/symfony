<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


class Codepromo
{
 
    #[ORM\Id]

    #[ORM\GeneratedValue]
    
    #[ORM\Column]
    
    private ?int $idcode= null;
    
    #[ORM\Column (length: 255)] private ?int $userid = null;
    #[ORM\Column (length: 255)] private ?int $idcategoriecode = null;


    public function getIdcode(): ?int
    {
        return $this->idcode;
    }

    public function getUserid(): ?int
    {
        return $this->userid;
    }

    public function setUserid(int $userid): static
    {
        $this->userid = $userid;

        return $this;
    }

    public function getIdcategoriecode(): ?int
    {
        return $this->idcategoriecode;
    }

    public function setIdcategoriecode(int $idcategoriecode): static
    {
        $this->idcategoriecode = $idcategoriecode;

        return $this;
    }


}
