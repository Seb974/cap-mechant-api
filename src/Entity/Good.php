<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\GoodRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=GoodRepository::class)
 * @ApiResource(
 *     denormalizationContext={
 *          "disable_type_enforcement"=true,
 *          "groups"={"provision_write"}
 *     },
 *     normalizationContext={"groups"={"goods_read"}},
 *     collectionOperations={
 *          "GET",
 *          "POST"={"security"="is_granted('ROLE_SELLER')"},
 *     },
 *     itemOperations={
 *          "GET",
 *          "PUT"={"security"="is_granted('ROLE_SELLER')"},
 *          "PATCH"={"security"="is_granted('ROLE_SELLER')"},
 *          "DELETE"={"security"="is_granted('ROLE_SELLER')"}
 *     },
 * )
 */
class Good
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"provisions_read", "provision_write", "goods_read"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class)
     * @Groups({"provisions_read", "provision_write", "goods_read"})
     */
    private $product;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"provisions_read", "provision_write", "goods_read"})
     */
    private $quantity;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     * @Groups({"provisions_read", "provision_write", "goods_read"})
     */
    private $unit;

    /*
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"provisions_read", "provision_write", "goods_read"})
     */
    // private $price;

    /**
     * @ORM\ManyToOne(targetEntity=Provision::class, inversedBy="goods")
     * @Groups({"goods_read"})
     */
    private $provision;

    /*
     * @ORM\ManyToOne(targetEntity=Variation::class)
     * @Groups({"provisions_read", "provision_write", "goods_read"})
     */
    // private $variation;

    /*
     * @ORM\ManyToOne(targetEntity=Size::class)
     * @Groups({"provisions_read", "provision_write", "goods_read"})
     */
    // private $size;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"provisions_read", "provision_write", "goods_read"})
     */
    private $received;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"provisions_read", "provision_write", "goods_read"})
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

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(?float $quantity): self
    {
        $this->quantity = $quantity;

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

    // public function getPrice(): ?float
    // {
    //     return $this->price;
    // }

    // public function setPrice(?float $price): self
    // {
    //     $this->price = $price;

    //     return $this;
    // }

    public function getProvision(): ?Provision
    {
        return $this->provision;
    }

    public function setProvision(?Provision $provision): self
    {
        $this->provision = $provision;

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

    public function getReceived(): ?float
    {
        return $this->received;
    }

    public function setReceived(?float $received): self
    {
        $this->received = $received;

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
