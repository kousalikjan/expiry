<?php

namespace App\Entity;

use App\Repository\WarrantyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WarrantyRepository::class)]
class Warranty
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $expiration = null;

    #[ORM\Column(nullable: true)]
    private ?int $notifyDaysBefore = null;

    #[ORM\OneToOne(inversedBy: 'warranty', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $item = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpiration(): ?\DateTimeInterface
    {
        return $this->expiration;
    }

    public function setExpiration(?\DateTimeInterface $expiration): self
    {
        $this->expiration = $expiration;

        return $this;
    }

    public function getNotifyDaysBefore(): ?int
    {
        return $this->notifyDaysBefore;
    }

    public function setNotifyDaysBefore(?int $notifyDaysBefore): self
    {
        $this->notifyDaysBefore = $notifyDaysBefore;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(Item $item): self
    {
        $this->item = $item;

        return $this;
    }
}
