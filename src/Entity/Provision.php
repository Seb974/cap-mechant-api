<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProvisionRepository;
use ApiPlatform\Core\Annotation\ApiFilter;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ORM\Entity(repositoryClass=ProvisionRepository::class)
 * @ApiResource(
 *     mercure={"private": true},
 *     denormalizationContext={
 *          "disable_type_enforcement"=true,
 *          "groups"={"provision_write"}
 *     },
 *     normalizationContext={"groups"={"provisions_read"}},
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_SELLER')"},
 *         "post"={"security"="is_granted('ROLE_SELLER')"},
 *     },
 *     itemOperations={
 *         "get"={"security"="is_granted('ROLE_SELLER')"},
 *         "put"={"security"="is_granted('ROLE_SELLER')"},
 *         "patch"={"security"="is_granted('ROLE_SELLER')"},
 *         "delete"={"security"="is_granted('ROLE_SELLER')"},
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={"supplier"="exact", "seller"="exact"})
 * @ApiFilter(DateFilter::class, properties={"provisionDate"})
 */
class Provision
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"provisions_read", "provision_write"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Supplier::class)
     * @Groups({"provisions_read", "provision_write"})
     */
    private $supplier;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"provisions_read", "provision_write"})
     */
    private $provisionDate;

    /**
     * @ORM\OneToMany(targetEntity=Good::class, mappedBy="provision", cascade={"persist", "remove"})
     * @Groups({"provisions_read", "provision_write"})
     */
    private $goods;

    /**
     * @ORM\ManyToOne(targetEntity=Seller::class)
     * @Groups({"provisions_read", "provision_write"})
     */
    private $seller;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     * @Groups({"provisions_read", "provision_write"})
     */
    private $status;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"provisions_read", "provision_write"})
     */
    private $integrated;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"provisions_read", "provision_write"})
     */
    private $sendingMode;

    /**
     * @ORM\ManyToOne(targetEntity=Meta::class)
     * @Groups({"provisions_read", "provision_write"})
     */
    private $metas;

    public function __construct()
    {
        $this->goods = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProvisionDate(): ?\DateTimeInterface
    {
        return $this->provisionDate;
    }

    public function setProvisionDate(?\DateTimeInterface $provisionDate): self
    {
        $this->provisionDate = $provisionDate;

        return $this;
    }

    /**
     * @return Collection|Good[]
     */
    public function getGoods(): Collection
    {
        return $this->goods;
    }

    public function addGood(Good $good): self
    {
        if (!$this->goods->contains($good)) {
            $this->goods[] = $good;
            $good->setProvision($this);
        }

        return $this;
    }

    public function removeGood(Good $good): self
    {
        if ($this->goods->removeElement($good)) {
            // set the owning side to null (unless already changed)
            if ($good->getProvision() === $this) {
                $good->setProvision(null);
            }
        }

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getIntegrated(): ?bool
    {
        return $this->integrated;
    }

    public function setIntegrated(?bool $integrated): self
    {
        $this->integrated = $integrated;

        return $this;
    }

    public function getSendingMode(): ?string
    {
        return $this->sendingMode;
    }

    public function setSendingMode(?string $sendingMode): self
    {
        $this->sendingMode = $sendingMode;

        return $this;
    }

    public function getMetas(): ?Meta
    {
        return $this->metas;
    }

    public function setMetas(?Meta $metas): self
    {
        $this->metas = $metas;

        return $this;
    }
}
