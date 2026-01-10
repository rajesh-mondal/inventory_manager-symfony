<?php

namespace App\Entity;

use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
class Inventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    // #[ORM\Column(length: 255)]
    // private ?string $category = null;

    #[ORM\ManyToOne(inversedBy: 'inventories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    /**
     * @var Collection<int, Item>
     */
    #[ORM\OneToMany(targetEntity: Item::class, mappedBy: 'inventory', orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column]
    private ?bool $is_public = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $id_pattern = null;

    #[ORM\Column(nullable: true)]
    private ?array $tags = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customString1Name = null;
    #[ORM\Column]
    private bool $customString1State = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customString2Name = null;
    #[ORM\Column]
    private bool $customString2State = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customString3Name = null;
    #[ORM\Column]
    private bool $customString3State = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customInt1Name = null;
    #[ORM\Column]
    private bool $customInt1State = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customInt2Name = null;
    #[ORM\Column]
    private bool $customInt2State = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customInt3Name = null;
    #[ORM\Column]
    private bool $customInt3State = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customBool1Name = null;
    #[ORM\Column]
    private bool $customBool1State = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customBool2Name = null;
    #[ORM\Column]
    private bool $customBool2State = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customBool3Name = null;
    #[ORM\Column]
    private bool $customBool3State = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customText1Name = null;
    #[ORM\Column]
    private bool $customText1State = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customText2Name = null;
    #[ORM\Column]
    private bool $customText2State = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customText3Name = null;
    #[ORM\Column]
    private bool $customText3State = false;

    #[ORM\ManyToOne(inversedBy: 'inventories')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $category = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'inventory', orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'inventory_write_access')]
    private Collection $writeAccessUsers;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->writeAccessUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    // public function getCategory(): ?string
    // {
    //     return $this->category;
    // }

    // public function setCategory(string $category): static
    // {
    //     $this->category = $category;

    //     return $this;
    // }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Collection<int, Item>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Item $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setInventory($this);
        }

        return $this;
    }

    public function removeItem(Item $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getInventory() === $this) {
                $item->setInventory(null);
            }
        }

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->is_public;
    }

    public function setIsPublic(bool $is_public): static
    {
        $this->is_public = $is_public;

        return $this;
    }

    public function getIdPattern(): ?string
    {
        return $this->id_pattern;
    }

    public function setIdPattern(?string $id_pattern): static
    {
        $this->id_pattern = $id_pattern;

        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function getCustomString1Name(): ?string
    {
        return $this->customString1Name;
    }

    public function setCustomString1Name(?string $customString1Name): static
    {
        $this->customString1Name = $customString1Name;

        return $this;
    }

    public function isCustomString1State(): ?bool
    {
        return $this->customString1State;
    }

    public function setCustomString1State(bool $customString1State): static
    {
        $this->customString1State = $customString1State;

        return $this;
    }

    public function getCustomString2Name(): ?string
    {
        return $this->customString2Name;
    }

    public function setCustomString2Name(?string $customString2Name): static
    {
        $this->customString2Name = $customString2Name;

        return $this;
    }

    public function isCustomString2State(): ?bool
    {
        return $this->customString2State;
    }

    public function setCustomString2State(bool $customString2State): static
    {
        $this->customString2State = $customString2State;

        return $this;
    }

    public function getCustomString3Name(): ?string
    {
        return $this->customString3Name;
    }

    public function setCustomString3Name(?string $customString3Name): static
    {
        $this->customString3Name = $customString3Name;

        return $this;
    }

    public function isCustomString3State(): ?bool
    {
        return $this->customString3State;
    }

    public function setCustomString3State(bool $customString3State): static
    {
        $this->customString3State = $customString3State;

        return $this;
    }

    public function getCustomInt1Name(): ?string
    {
        return $this->customInt1Name;
    }

    public function setCustomInt1Name(?string $customInt1Name): static
    {
        $this->customInt1Name = $customInt1Name;

        return $this;
    }

    public function isCustomInt1State(): ?bool
    {
        return $this->customInt1State;
    }

    public function setCustomInt1State(bool $customInt1State): static
    {
        $this->customInt1State = $customInt1State;

        return $this;
    }

    public function getCustomInt2Name(): ?string
    {
        return $this->customInt2Name;
    }

    public function setCustomInt2Name(?string $customInt2Name): static
    {
        $this->customInt2Name = $customInt2Name;

        return $this;
    }

    public function isCustomInt2State(): ?bool
    {
        return $this->customInt2State;
    }

    public function setCustomInt2State(bool $customInt2State): static
    {
        $this->customInt2State = $customInt2State;

        return $this;
    }

    public function getCustomInt3Name(): ?string
    {
        return $this->customInt3Name;
    }

    public function setCustomInt3Name(?string $customInt3Name): static
    {
        $this->customInt3Name = $customInt3Name;

        return $this;
    }

    public function isCustomInt3State(): ?bool
    {
        return $this->customInt3State;
    }

    public function setCustomInt3State(bool $customInt3State): static
    {
        $this->customInt3State = $customInt3State;

        return $this;
    }

    public function getCustomBool1Name(): ?string
    {
        return $this->customBool1Name;
    }

    public function setCustomBool1Name(?string $customBool1Name): static
    {
        $this->customBool1Name = $customBool1Name;

        return $this;
    }

    public function isCustomBool1State(): ?bool
    {
        return $this->customBool1State;
    }

    public function setCustomBool1State(bool $customBool1State): static
    {
        $this->customBool1State = $customBool1State;

        return $this;
    }

    public function getCustomBool2Name(): ?string
    {
        return $this->customBool2Name;
    }

    public function setCustomBool2Name(?string $customBool2Name): static
    {
        $this->customBool2Name = $customBool2Name;

        return $this;
    }

    public function isCustomBool2State(): ?bool
    {
        return $this->customBool2State;
    }

    public function setCustomBool2State(bool $customBool2State): static
    {
        $this->customBool2State = $customBool2State;

        return $this;
    }

    public function getCustomBool3Name(): ?string
    {
        return $this->customBool3Name;
    }

    public function setCustomBool3Name(?string $customBool3Name): static
    {
        $this->customBool3Name = $customBool3Name;

        return $this;
    }

    public function isCustomBool3State(): ?bool
    {
        return $this->customBool3State;
    }

    public function setCustomBool3State(bool $customBool3State): static
    {
        $this->customBool3State = $customBool3State;

        return $this;
    }

    public function getCustomText1Name(): ?string
    {
        return $this->customText1Name;
    }

    public function setCustomText1Name(?string $customText1Name): static
    {
        $this->customText1Name = $customText1Name;

        return $this;
    }

    public function isCustomText1State(): ?bool
    {
        return $this->customText1State;
    }

    public function setCustomText1State(bool $customText1State): static
    {
        $this->customText1State = $customText1State;

        return $this;
    }

    public function getCustomText2Name(): ?string
    {
        return $this->customText2Name;
    }

    public function setCustomText2Name(?string $customText2Name): static
    {
        $this->customText2Name = $customText2Name;

        return $this;
    }

    public function isCustomText2State(): ?bool
    {
        return $this->customText2State;
    }

    public function setCustomText2State(bool $customText2State): static
    {
        $this->customText2State = $customText2State;

        return $this;
    }

    public function getCustomText3Name(): ?string
    {
        return $this->customText3Name;
    }

    public function setCustomText3Name(?string $customText3Name): static
    {
        $this->customText3Name = $customText3Name;

        return $this;
    }

    public function isCustomText3State(): ?bool
    {
        return $this->customText3State;
    }

    public function setCustomText3State(bool $customText3State): static
    {
        $this->customText3State = $customText3State;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setInventory($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getInventory() === $this) {
                $comment->setInventory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getWriteAccessUsers(): Collection
    {
        return $this->writeAccessUsers;
    }

    public function addWriteAccessUser(User $user): static
    {
        if (!$this->writeAccessUsers->contains($user)) {
            $this->writeAccessUsers->add($user);
        }
        return $this;
    }

    public function removeWriteAccessUser(User $user): static
    {
        $this->writeAccessUsers->removeElement($user);
        return $this;
    }
}
