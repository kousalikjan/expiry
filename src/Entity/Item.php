<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 20)]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private ?int $amount = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $purchase = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\OneToOne(mappedBy: 'item', cascade: ['persist', 'remove'])]
    private ?Warranty $warranty = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(max: 20)]
    private ?string $vendor = null;

    #[ORM\Column(nullable: true)]
    private ?int $price = null;

    #[ORM\Column(length: 3, nullable: true)]
    #[Assert\Length(max: 3)]
    private ?string $currency = null;

    #[ORM\Column(length: 128, nullable: true)]
    #[Assert\Length(max: 128)]
    private ?string $barcode = null;

    #[ORM\Column(length: 40, nullable: true)]
    #[Assert\Length(max: 40)]
    private ?string $note = null;

    #[ORM\OneToMany(mappedBy: 'item', targetEntity: ItemFile::class)]
    private Collection $itemFiles;

    public function __construct()
    {
        $this->purchase = new \DateTime();
        $this->amount = 1;
        $this->itemFiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPurchase(): ?\DateTimeInterface
    {
        return $this->purchase;
    }

    public function setPurchase(?\DateTimeInterface $purchase): self
    {
        $this->purchase = $purchase;

        return $this;
    }


    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getWarranty(): ?Warranty
    {
        return $this->warranty;
    }

    public function setWarranty(?Warranty $warranty): self
    {
        // set the owning side of the relation if necessary
        if ($warranty->getItem() !== $this) {
            $warranty->setItem($this);
        }

        $this->warranty = $warranty;

        return $this;
    }

    public function getVendor(): ?string
    {
        return $this->vendor;
    }

    public function setVendor(?string $vendor): self
    {
        $this->vendor = $vendor;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(?string $barcode): self
    {
        $this->barcode = $barcode;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @return Collection<int, ItemFile>
     */
    public function getItemFiles(): Collection
    {
        return $this->itemFiles;
    }

    public function warrantyToggled(): bool {
        return $this->getWarranty() !== null;
    }

    public function additionalToggled(): bool {
        return $this->getPrice() !== null ||
            $this->getVendor() !== null ||
            $this->getBarcode() !== null ||
            $this->getNote() !== null;
    }

    public function setNullWarranty(): self {
        $this->warranty = null;

        return $this;
    }

}
