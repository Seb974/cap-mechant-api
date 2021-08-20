<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrderEntityRepository;
use ApiPlatform\Core\Annotation\ApiFilter;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ORM\Entity(repositoryClass=OrderEntityRepository::class)
 * @ApiResource(
 *     denormalizationContext={
 *          "disable_type_enforcement"=true,
 *          "groups"={"order_write"}
 *     },
 *     normalizationContext={
 *          "groups"={"orders_read", "admin:orders_read"},
 *          "enable_max_depth"=true
 *     },
 *     collectionOperations={
 *          "GET"={"security"="is_granted('ROLE_TEAM')"},
 *          "POST"
 *     },
 *     itemOperations={
 *          "GET"={"security"="is_granted('ROLE_TEAM') or object.getUser() == user"},
 *          "PUT"={"security"="is_granted('ROLE_TEAM') or object.isOwner(request, object)"},
 *          "PATCH"={"security"="is_granted('ROLE_TEAM')"},
 *          "DELETE"={"security"="is_granted('ROLE_TEAM') or object.isOwner(request, object)"}
 *     },
 *     mercure="object.getMercureOptions(object.getUser())"
 * )
 * @ApiFilter(SearchFilter::class, properties={"status"="partial"})
 * @ApiFilter(DateFilter::class, properties={"deliveryDate"})
 */
class OrderEntity
{
    /**
     * server domain, used to configure the Mercure hub topics
     */
    private static $domain = 'http://localhost:8000';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"orders_read", "order_write", "tourings_read", "touring_write"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     * @Groups({"orders_read", "order_write", "tourings_read"})
     */
    private $name;

    /*
     * @ORM\Column(type="string", length=120, nullable=true)
     * @Groups({"orders_read", "order_write", "tourings_read"})
     */
    // private $email;

    /**
     * @ORM\ManyToOne(targetEntity=Meta::class, cascade={"persist"})
     * @Groups({"admin:orders_read", "order_write", "tourings_read"})
     */
    private $metas;

    /**
     * @ORM\OneToMany(targetEntity=Item::class, mappedBy="orderEntity", cascade={"persist", "remove"})
     * @Groups({"orders_read", "order_write", "tourings_read", "touring_write"})
     */
    private $items;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"orders_read", "order_write", "tourings_read"})
     */
    private $deliveryDate;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"orders_read", "order_write", "tourings_read", "touring_write"})
     */
    private $status;

    /*
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"admin:orders_read", "order_write", "tourings_read"})
     */
    // private $isRemains;

    /*
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"orders_read", "order_write", "tourings_read"})
     */
    // private $totalHT;

    /*
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"orders_read", "order_write", "tourings_read"})
     */
    // private $totalTTC;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @Groups({"admin:orders_read", "order_write", "tourings_read"})
     */
    private $user;

    /*
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"orders_read", "order_write", "tourings_read"})
     */
    // private $message;

    /*
     * @ORM\ManyToOne(targetEntity=Catalog::class)
     * @Groups({"admin:orders_read", "order_write"})
     */
    // private $catalog;

    /*
     * @ORM\ManyToOne(targetEntity=Promotion::class)
     * @Groups({"admin:orders_read", "order_write"})
     */
    // private $promotion;

    /*
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"orders_read", "order_write"})
     */
    // private $paymentId;

    /*
     * @ORM\Column(type="guid", nullable=true)
     * @Groups({"admin:orders_read", "order_write"})
     */
    // private $uuid;

    /*
     * @ORM\ManyToOne(targetEntity=Condition::class)
     * @Groups({"admin:orders_read", "order_write"})
     */
    // private $appliedCondition;

    /*
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"admin:orders_read", "order_write", "tourings_read", "touring_write"})
     */
    // private $deliveryPriority;

    /*
     * @ORM\ManyToOne(targetEntity=Touring::class, inversedBy="orderEntities")
     * @Groups({"orders_read"})
     */
    // private $touring;

    /*
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"admin:orders_read", "order_write", "tourings_read", "touring_write"})
     */
    // private $regulated;

    /*
     * @ORM\OneToMany(targetEntity=Package::class, mappedBy="orderEntity", cascade={"persist", "remove"})
     * @Groups({"orders_read", "order_write", "tourings_read", "touring_write"})
     */
    // private $packages;

    /*
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"orders_read", "order_write"})
     */
    // private $trackIds = [];

    /*
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"orders_read", "order_write"})
     */
    // private $reservationNumber;

    /*
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"admin:orders_read", "order_write"})
     */
    // private $invoiced;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $emails = [];

    public function __construct()
    {
        $this->items = new ArrayCollection();
        // $this->packages = new ArrayCollection();
    }

    public function isOwner($request, $object)
    {
        $data = $request->query->get('id');
        return $object->getUuid() === $data;
    }

    public function getMercureOptions($user): array
    {
        $id = $user != null ? $user->getId() : 0;
        return ["private" => true, "topics" => self::$domain . "/api/users/" . $id . "/shipments"];
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

    // public function getEmail(): ?string
    // {
    //     return $this->email;
    // }

    // public function setEmail(?string $email): self
    // {
    //     $this->email = $email;

    //     return $this;
    // }

    public function getMetas(): ?Meta
    {
        return $this->metas;
    }

    public function setMetas(?Meta $metas): self
    {
        $this->metas = $metas;

        return $this;
    }

    /**
     * @return Collection|Item[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Item $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setOrderEntity($this);
        }

        return $this;
    }

    public function removeItem(Item $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getOrderEntity() === $this) {
                $item->setOrderEntity(null);
            }
        }

        return $this;
    }

    public function getDeliveryDate(): ?\DateTimeInterface
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(?\DateTimeInterface $deliveryDate): self
    {
        $this->deliveryDate = $deliveryDate;

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

    // public function getIsRemains(): ?bool
    // {
    //     return $this->isRemains;
    // }

    // public function setIsRemains(?bool $isRemains): self
    // {
    //     $this->isRemains = $isRemains;

    //     return $this;
    // }

    // public function getTotalHT(): ?float
    // {
    //     return $this->totalHT;
    // }

    // public function setTotalHT(?float $totalHT): self
    // {
    //     $this->totalHT = $totalHT;

    //     return $this;
    // }

    // public function getTotalTTC(): ?float
    // {
    //     return $this->totalTTC;
    // }

    // public function setTotalTTC(?float $totalTTC): self
    // {
    //     $this->totalTTC = $totalTTC;

    //     return $this;
    // }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    // public function getMessage(): ?string
    // {
    //     return $this->message;
    // }

    // public function setMessage(?string $message): self
    // {
    //     $this->message = $message;

    //     return $this;
    // }

    // public function getCatalog(): ?Catalog
    // {
    //     return $this->catalog;
    // }

    // public function setCatalog(?Catalog $catalog): self
    // {
    //     $this->catalog = $catalog;

    //     return $this;
    // }

    // public function getPromotion(): ?Promotion
    // {
    //     return $this->promotion;
    // }

    // public function setPromotion(?Promotion $promotion): self
    // {
    //     $this->promotion = $promotion;

    //     return $this;
    // }

    // public function getPaymentId(): ?string
    // {
    //     return $this->paymentId;
    // }

    // public function setPaymentId(?string $paymentId): self
    // {
    //     $this->paymentId = $paymentId;

    //     return $this;
    // }

    // public function getUuid(): ?string
    // {
    //     return $this->uuid;
    // }

    // public function setUuid(?string $uuid): self
    // {
    //     $this->uuid = $uuid;

    //     return $this;
    // }

    // public function getAppliedCondition(): ?Condition
    // {
    //     return $this->appliedCondition;
    // }

    // public function setAppliedCondition(?Condition $appliedCondition): self
    // {
    //     $this->appliedCondition = $appliedCondition;

    //     return $this;
    // }

    // public function getDeliveryPriority(): ?int
    // {
    //     return $this->deliveryPriority;
    // }

    // public function setDeliveryPriority(?int $deliveryPriority): self
    // {
    //     $this->deliveryPriority = $deliveryPriority;

    //     return $this;
    // }

    // public function getTouring(): ?Touring
    // {
    //     return $this->touring;
    // }

    // public function setTouring(?Touring $touring): self
    // {
    //     $this->touring = $touring;

    //     return $this;
    // }

    // public function getRegulated(): ?bool
    // {
    //     return $this->regulated;
    // }

    // public function setRegulated(?bool $regulated): self
    // {
    //     $this->regulated = $regulated;

    //     return $this;
    // }

    /*
     * @return Collection|Package[]
     */
    // public function getPackages(): Collection
    // {
    //     return $this->packages;
    // }

    // public function addPackage(Package $package): self
    // {
    //     if (!$this->packages->contains($package)) {
    //         $this->packages[] = $package;
    //         $package->setOrderEntity($this);
    //     }

    //     return $this;
    // }

    // public function removePackage(Package $package): self
    // {
    //     if ($this->packages->removeElement($package)) {
    //         // set the owning side to null (unless already changed)
    //         if ($package->getOrderEntity() === $this) {
    //             $package->setOrderEntity(null);
    //         }
    //     }

    //     return $this;
    // }

    // public function getTrackIds(): ?array
    // {
    //     return $this->trackIds;
    // }

    // public function setTrackIds(?array $trackIds): self
    // {
    //     $this->trackIds = $trackIds;

    //     return $this;
    // }

    // public function getReservationNumber(): ?string
    // {
    //     return $this->reservationNumber;
    // }

    // public function setReservationNumber(?string $reservationNumber): self
    // {
    //     $this->reservationNumber = $reservationNumber;

    //     return $this;
    // }

    // public function getInvoiced(): ?bool
    // {
    //     return $this->invoiced;
    // }

    // public function setInvoiced(?bool $invoiced): self
    // {
    //     $this->invoiced = $invoiced;

    //     return $this;
    // }

    public function getEmails(): ?array
    {
        return $this->emails;
    }

    public function setEmails(?array $emails): self
    {
        $this->emails = $emails;

        return $this;
    }
}
