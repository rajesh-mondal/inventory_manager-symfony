<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Inventory $inventory = null;

    #[ORM\Column(length: 255)]
    private ?string $customId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stringVal1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stringVal2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stringVal3 = null;

    #[ORM\Column(nullable: true)]
    private ?int $intVal1 = null;

    #[ORM\Column(nullable: true)]
    private ?int $intVal2 = null;

    #[ORM\Column(nullable: true)]
    private ?int $intVal3 = null;

    #[ORM\Column(nullable: true)]
    private ?bool $boolVal1 = null;

    #[ORM\Column(nullable: true)]
    private ?bool $boolVal2 = null;

    #[ORM\Column(nullable: true)]
    private ?bool $boolVal3 = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $textVal1 = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $textVal2 = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $textVal3 = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getInventory(): ?Inventory
    {
        return $this->inventory;
    }

    public function setInventory(?Inventory $inventory): static
    {
        $this->inventory = $inventory;

        return $this;
    }

    public function getCustomId(): ?string
    {
        return $this->customId;
    }

    public function setCustomId(string $customId): static
    {
        $this->customId = $customId;

        return $this;
    }

    public function getStringVal1(): ?string
    {
        return $this->stringVal1;
    }

    public function setStringVal1(?string $stringVal1): static
    {
        $this->stringVal1 = $stringVal1;

        return $this;
    }

    public function getStringVal2(): ?string
    {
        return $this->stringVal2;
    }

    public function setStringVal2(?string $stringVal2): static
    {
        $this->stringVal2 = $stringVal2;

        return $this;
    }

    public function getStringVal3(): ?string
    {
        return $this->stringVal3;
    }

    public function setStringVal3(?string $stringVal3): static
    {
        $this->stringVal3 = $stringVal3;

        return $this;
    }

    public function getIntVal1(): ?int
    {
        return $this->intVal1;
    }

    public function setIntVal1(?int $intVal1): static
    {
        $this->intVal1 = $intVal1;

        return $this;
    }

    public function getIntVal2(): ?int
    {
        return $this->intVal2;
    }

    public function setIntVal2(?int $intVal2): static
    {
        $this->intVal2 = $intVal2;

        return $this;
    }

    public function getIntVal3(): ?int
    {
        return $this->intVal3;
    }

    public function setIntVal3(?int $intVal3): static
    {
        $this->intVal3 = $intVal3;

        return $this;
    }

    public function isBoolVal1(): ?bool
    {
        return $this->boolVal1;
    }

    public function setBoolVal1(?bool $boolVal1): static
    {
        $this->boolVal1 = $boolVal1;

        return $this;
    }

    public function isBoolVal2(): ?bool
    {
        return $this->boolVal2;
    }

    public function setBoolVal2(?bool $boolVal2): static
    {
        $this->boolVal2 = $boolVal2;

        return $this;
    }

    public function isBoolVal3(): ?bool
    {
        return $this->boolVal3;
    }

    public function setBoolVal3(?bool $boolVal3): static
    {
        $this->boolVal3 = $boolVal3;

        return $this;
    }

    public function getTextVal1(): ?string
    {
        return $this->textVal1;
    }

    public function setTextVal1(?string $textVal1): static
    {
        $this->textVal1 = $textVal1;

        return $this;
    }

    public function getTextVal2(): ?string
    {
        return $this->textVal2;
    }

    public function setTextVal2(?string $textVal2): static
    {
        $this->textVal2 = $textVal2;

        return $this;
    }

    public function getTextVal3(): ?string
    {
        return $this->textVal3;
    }

    public function setTextVal3(?string $textVal3): static
    {
        $this->textVal3 = $textVal3;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
