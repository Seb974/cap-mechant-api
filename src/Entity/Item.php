<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ItemRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ItemRepository::class)
 * @ApiResource(
 *     denormalizationContext={
 *          "disable_type_enforcement"=true,
 *          "groups"={"order_write", "touring_write"}
 *     },
 *     normalizationContext={"groups"={"items_read"}},
 *     collectionOperations={
 *          "GET"={"security"="is_granted('ROLE_TEAM') or object.orderEntity.getUser() == user"},
 *          "POST"
 *     },
 *     itemOperations={
 *          "GET"={"security"="is_granted('ROLE_TEAM') or object.orderEntity.getUser() == user"},
 *          "PUT"={"security"="is_granted('ROLE_TEAM')"},
 *          "PATCH"={"security"="is_granted('ROLE_TEAM')"},
 *          "DELETE"={"security"="is_granted('ROLE_TEAM')"}
 *     },
 * )
 */
class Item
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"items_read", "orders_read", "tourings_read", "order_write", "touring_write"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class)
     * @Groups({"items_read", "orders_read", "order_write", "tourings_read", "admin:orders_read"})
     */
    private $product;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"items_read", "orders_read", "order_write", "tourings_read"})
     */
    private $orderedQty;

    /*
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"items_read", "orders_read", "admin:order_write", "tourings_read"})
     */
    // private $preparedQty;

    /*
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"items_read", "orders_read", "admin:order_write", "tourings_read", "touring_write"})
     */
    // private $deliveredQty;

    /*
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"items_read", "orders_read", "admin:order_write", "tourings_read"})
     */
    // private $price;

    /*
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"items_read", "orders_read", "admin:order_write", "tourings_read"})
     */
    // private $taxRate;

    /*
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"admin:items_read", "admin:orders_read", "admin:order_write", "tourings_read"})
     */
    // private $isAdjourned;

    /**
     * @ORM\ManyToOne(targetEntity=OrderEntity::class, inversedBy="items")
     * @Groups({"admin:items_read"})
     */
    private $orderEntity;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"items_read", "orders_read", "order_write", "tourings_read"})
     */
    private $isPrepared;

    /*
     * @ORM\ManyToOne(targetEntity=Variation::class)
     * @Groups({"items_read", "orders_read", "order_write", "tourings_read"})
     */
    // private $variation;

    /*
     * @ORM\ManyToOne(targetEntity=Size::class)
     * @Groups({"items_read", "orders_read", "order_write", "tourings_read"})
     */
    // private $size;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({"items_read", "orders_read", "order_write", "tourings_read"})
     */
    private $unit;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"items_read", "orders_read", "order_write", "tourings_read"})
     */
    private $stock;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getOrderedQty(): ?float
    {
        return $this->orderedQty;
    }

    public function setOrderedQty(?float $orderedQty): self
    {
        $this->orderedQty = $orderedQty;

        return $this;
    }

    // public function getPreparedQty(): ?float
    // {
    //     return $this->preparedQty;
    // }

    // public function setPreparedQty(?float $preparedQty): self
    // {
    //     $this->preparedQty = $preparedQty;

    //     return $this;
    // }

    // public function getDeliveredQty(): ?float
    // {
    //     return $this->deliveredQty;
    // }

    // public function setDeliveredQty(?float $deliveredQty): self
    // {
    //     $this->deliveredQty = $deliveredQty;

    //     return $this;
    // }

    // public function getPrice(): ?float
    // {
    //     return $this->price;
    // }

    // public function setPrice(?float $price): self
    // {
    //     $this->price = $price;

    //     return $this;
    // }

    // public function getTaxRate(): ?float
    // {
    //     return $this->taxRate;
    // }

    // public function setTaxRate(?float $taxRate): self
    // {
    //     $this->taxRate = $taxRate;

    //     return $this;
    // }

    // public function getIsAdjourned(): ?bool
    // {
    //     return $this->isAdjourned;
    // }

    // public function setIsAdjourned(?bool $isAdjourned): self
    // {
    //     $this->isAdjourned = $isAdjourned;

    //     return $this;
    // }

    public function getOrderEntity(): ?OrderEntity
    {
        return $this->orderEntity;
    }

    public function setOrderEntity(?OrderEntity $orderEntity): self
    {
        $this->orderEntity = $orderEntity;

        return $this;
    }

    public function getIsPrepared(): ?bool
    {
        return $this->isPrepared;
    }

    public function setIsPrepared(?bool $isPrepared): self
    {
        $this->isPrepared = $isPrepared;

        return $this;
    }

    // public function getVariation(): ?Variation
    // {
    //     return $this->variation;
    // }

    // public function setVariation(?Variation $variation): self
    // {
    //     $this->variation = $variation;

    //     return $this;
    // }

    // public function getSize(): ?Size
    // {
    //     return $this->size;
    // }

    // public function setSize(?Size $size): self
    // {
    //     $this->size = $size;

    //     return $this;
    // }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getStock(): ?float
    {
        return $this->stock;
    }

    public function setStock(?float $stock): self
    {
        $this->stock = $stock;

        return $this;
    }
}
