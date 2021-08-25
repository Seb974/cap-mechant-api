<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PriceRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PriceRepository::class)
 * @ApiResource(
 *      mercure="object.getMercureOptions(object.getProduct())",
 *      normalizationContext={"groups"={"price_read"}},
 *      collectionOperations={
 *          "GET",
 *          "POST"={"security"="is_granted('ROLE_ADMIN')"},
 *     },
 *     itemOperations={
 *          "GET",
 *          "PUT"={"security"="is_granted('ROLE_ADMIN')"},
 *          "PATCH"={"security"="is_granted('ROLE_ADMIN')"},
 *          "DELETE"={"security"="is_granted('ROLE_ADMIN')"}
 *     }
 * )
 */
class Price
{
    /**
     * server domain, used to configure the Mercure hub topics
     */
    private static $domain = 'http://localhost:8000';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"price_read", "products_read", "product_write"})
     */
    private $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"price_read", "products_read", "product_write"})
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity=PriceGroup::class)
     * @Groups({"price_read", "products_read", "product_write"})
     */
    private $priceGroup;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="prices")
     * @Groups({"price_read"})
     */
    private $product;

    public function getMercureOptions($product): array
    {
        return [
            "private" => false, 
            "topics" => self::$domain . "/api/products/" . $product->getId()
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPriceGroup(): ?PriceGroup
    {
        return $this->priceGroup;
    }

    public function setPriceGroup(?PriceGroup $priceGroup): self
    {
        $this->priceGroup = $priceGroup;

        return $this;
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
}
