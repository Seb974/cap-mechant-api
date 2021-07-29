<?php

namespace App\Entity;

use App\Entity\Seller;
use App\Entity\Picture;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @ApiResource(
 *      attributes={"force_eager"=false},
 *      mercure={"private": false},
 *      denormalizationContext={
 *          "groups"={"product_write"},
 *          "disable_type_enforcement"=true
 *      },
 *      normalizationContext={
 *          "groups"={"products_read", "seller:products_read"}
 *      },
 *      collectionOperations={
 *          "GET",
 *          "POST"={"security"="is_granted('ROLE_TEAM')"},
 *     },
 *     itemOperations={
 *          "GET",
 *          "PUT"={"security"="is_granted('ROLE_TEAM')"},
 *          "PATCH"={"security"="is_granted('ROLE_TEAM')"},
 *          "DELETE"={"security"="is_granted('ROLE_TEAM')"}
 *     },
 * )
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"products_read", "orders_read", "tourings_read", "provisions_read", "goods_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     * @Groups({"products_read", "product_write", "orders_read", "tourings_read", "provisions_read", "goods_read"})
     * @Assert\NotBlank(message="Un nom est obligatoire.")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"products_read", "product_write"})
     */
    private $sku;

    /**
     * @var Picture|null
     * 
     * @ORM\OneToOne(targetEntity=Picture::class, cascade={"persist", "remove"})
     * @Groups({"products_read", "product_write", "orders_read", "tourings_read"})
     */
    private $image;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"products_read", "product_write"})
     */
    private $discount;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"products_read", "product_write"})
     */
    private $offerEnd;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"products_read", "product_write"})
     */
    private $fullDescription;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"products_read", "product_write"})
     */
    private $saleCount;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"products_read", "product_write"})
     */
    private $new;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"products_read", "product_write"})
     */
    private $available;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"products_read", "product_write"})
     */
    private $stockManaged;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"products_read", "product_write"})
     */
    private $requireLegalAge;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     * @Groups({"products_read", "product_write", "orders_read", "tourings_read", "provisions_read", "goods_read"})
     */
    private $unit;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"products_read", "product_write", "orders_read"})
     */
    private $weight;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Groups({"products_read", "product_write"})
     */
    private $productGroup;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"products_read", "product_write"})
     */
    private $isMixed;

    /**
     * @ORM\ManyToOne(targetEntity=Tax::class)
     * @Groups({"products_read", "product_write"})
     */
    private $tax;

    /**
     * @ORM\ManyToMany(targetEntity=Category::class)
     * @Groups({"products_read", "product_write"})
     */
    private $categories;

    /**
     * @ORM\OneToOne(targetEntity=Stock::class, cascade={"persist", "remove"})
     * @Groups({"products_read", "product_write", "admin:orders_read"})
     */
    private $stock;

    /**
     * @ORM\OneToMany(targetEntity=Variation::class, mappedBy="product")
     * @Groups({"products_read", "product_write"})
     */
    private $variations;

    /**
     * @ORM\OneToMany(targetEntity=Component::class, mappedBy="owner", cascade={"persist", "remove"})
     * @Groups({"products_read", "product_write"})
     */
    private $components;

    /**
     * @ORM\OneToMany(targetEntity=Price::class, mappedBy="product", cascade={"persist", "remove"})
     * @Groups({"products_read", "product_write"})
     */
    private $prices;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"products_read", "product_write"})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity=Group::class)
     * @Groups({"products_read", "product_write"})
     */
    private $userGroups;

    /**
     * @ORM\ManyToMany(targetEntity=Catalog::class)
     * @Groups({"products_read", "product_write"})
     */
    private $catalogs;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"seller:products_read", "product_write", "provisions_read", "goods_read"})
     */
    private $isFabricated;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"seller:products_read", "product_write", "provisions_read", "goods_read"})
     */
    private $isSold;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"seller:products_read", "provisions_read", "goods_read"})
     */
    private $lastCost;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"seller:products_read", "product_write"})
     */
    private $requireDeclaration;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"products_read", "product_write", "orders_read"})
     */
    private $contentWeight;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"seller:products_read", "product_write", "admin:orders_read"})
     */
    private $accountingId;

    /**
     * @ORM\ManyToOne(targetEntity=Seller::class)
     * @Groups({"products_read", "product_write", "orders_read"})
     */
    private $seller;

    /**
     * @ORM\ManyToOne(targetEntity=Supplier::class)
     * @Groups({"products_read", "product_write", "orders_read"})
     */
    private $supplier;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->variations = new ArrayCollection();
        $this->components = new ArrayCollection();
        $this->prices = new ArrayCollection();
        $this->userGroups = new ArrayCollection();
        $this->catalogs = new ArrayCollection();
        $this->sellers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getImage(): ?Picture
    {
        return $this->image;
    }

    public function setImage(?Picture $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function setDiscount(?float $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function getOfferEnd(): ?\DateTimeInterface
    {
        return $this->offerEnd;
    }

    public function setOfferEnd(?\DateTimeInterface $offerEnd): self
    {
        $this->offerEnd = $offerEnd;

        return $this;
    }

    public function getFullDescription(): ?string
    {
        return $this->fullDescription;
    }

    public function setFullDescription(?string $fullDescription): self
    {
        $this->fullDescription = $fullDescription;

        return $this;
    }

    public function getSaleCount(): ?int
    {
        return $this->saleCount;
    }

    public function setSaleCount(?int $saleCount): self
    {
        $this->saleCount = $saleCount;

        return $this;
    }

    public function getNew(): ?bool
    {
        return $this->new;
    }

    public function setNew(?bool $new): self
    {
        $this->new = $new;

        return $this;
    }

    public function getAvailable(): ?bool
    {
        return $this->available;
    }

    public function setAvailable(?bool $available): self
    {
        $this->available = $available;

        return $this;
    }

    public function getStockManaged(): ?bool
    {
        return $this->stockManaged;
    }

    public function setStockManaged(?bool $stockManaged): self
    {
        $this->stockManaged = $stockManaged;

        return $this;
    }

    public function getRequireLegalAge(): ?bool
    {
        return $this->requireLegalAge;
    }

    public function setRequireLegalAge(?bool $requireLegalAge): self
    {
        $this->requireLegalAge = $requireLegalAge;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getProductGroup(): ?string
    {
        return $this->productGroup;
    }

    public function setProductGroup(?string $productGroup): self
    {
        $this->productGroup = $productGroup;

        return $this;
    }

    public function getIsMixed(): ?bool
    {
        return $this->isMixed;
    }

    public function setIsMixed(?bool $isMixed): self
    {
        $this->isMixed = $isMixed;

        return $this;
    }

    public function getTax(): ?Tax
    {
        return $this->tax;
    }

    public function setTax(?Tax $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @return Collection|Variation[]
     */
    public function getVariations(): Collection
    {
        return $this->variations;
    }

    public function addVariation(Variation $variation): self
    {
        if (!$this->variations->contains($variation)) {
            $this->variations[] = $variation;
            $variation->setProduct($this);
        }

        return $this;
    }

    public function removeVariation(Variation $variation): self
    {
        if ($this->variations->removeElement($variation)) {
            // set the owning side to null (unless already changed)
            if ($variation->getProduct() === $this) {
                $variation->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Component[]
     */
    public function getComponents(): Collection
    {
        return $this->components;
    }

    public function addComponent(Component $component): self
    {
        if (!$this->components->contains($component)) {
            $this->components[] = $component;
            $component->setOwner($this);
        }

        return $this;
    }

    public function removeComponent(Component $component): self
    {
        if ($this->components->removeElement($component)) {
            // set the owning side to null (unless already changed)
            if ($component->getOwner() === $this) {
                $component->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Price[]
     */
    public function getPrices(): Collection
    {
        return $this->prices;
    }

    public function addPrice(Price $price): self
    {
        if (!$this->prices->contains($price)) {
            $this->prices[] = $price;
            $price->setProduct($this);
        }

        return $this;
    }

    public function removePrice(Price $price): self
    {
        if ($this->prices->removeElement($price)) {
            // set the owning side to null (unless already changed)
            if ($price->getProduct() === $this) {
                $price->setProduct(null);
            }
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|Group[]
     */
    public function getUserGroups(): Collection
    {
        return $this->userGroups;
    }

    public function addUserGroup(Group $userGroup): self
    {
        if (!$this->userGroups->contains($userGroup)) {
            $this->userGroups[] = $userGroup;
        }

        return $this;
    }

    public function removeUserGroup(Group $userGroup): self
    {
        $this->userGroups->removeElement($userGroup);

        return $this;
    }

    /**
     * @return Collection|Catalog[]
     */
    public function getCatalogs(): Collection
    {
        return $this->catalogs;
    }

    public function addCatalog(Catalog $catalog): self
    {
        if (!$this->catalogs->contains($catalog)) {
            $this->catalogs[] = $catalog;
        }

        return $this;
    }

    public function removeCatalog(Catalog $catalog): self
    {
        $this->catalogs->removeElement($catalog);

        return $this;
    }

    public function getIsFabricated(): ?bool
    {
        return $this->isFabricated;
    }

    public function setIsFabricated(?bool $isFabricated): self
    {
        $this->isFabricated = $isFabricated;

        return $this;
    }

    public function getIsSold(): ?bool
    {
        return $this->isSold;
    }

    public function setIsSold(?bool $isSold): self
    {
        $this->isSold = $isSold;

        return $this;
    }

    public function getLastCost(): ?float
    {
        return $this->lastCost;
    }

    public function setLastCost(?float $lastCost): self
    {
        $this->lastCost = $lastCost;

        return $this;
    }

    public function getRequireDeclaration(): ?bool
    {
        return $this->requireDeclaration;
    }

    public function setRequireDeclaration(?bool $requireDeclaration): self
    {
        $this->requireDeclaration = $requireDeclaration;

        return $this;
    }

    public function getContentWeight(): ?float
    {
        return $this->contentWeight;
    }

    public function setContentWeight(?float $contentWeight): self
    {
        $this->contentWeight = $contentWeight;

        return $this;
    }

    public function getAccountingId(): ?int
    {
        return $this->accountingId;
    }

    public function setAccountingId(?int $accountingId): self
    {
        $this->accountingId = $accountingId;

        return $this;
    }

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function setSeller(?Seller $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): self
    {
        $this->supplier = $supplier;

        return $this;
    }
}
