<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\SupplierRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SupplierRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"suppliers_read"}},
 *      collectionOperations={
 *          "GET"={"security"="is_granted('ROLE_TEAM')"},
 *          "POST"={"security"="is_granted('ROLE_SELLER')"},
 *     },
 *     itemOperations={
 *          "GET"={"security"="is_granted('ROLE_TEAM')"},
 *          "PUT"={"security"="is_granted('ROLE_SELLER')"},
 *          "PATCH"={"security"="is_granted('ROLE_SELLER')"},
 *          "DELETE"={"security"="is_granted('ROLE_SELLER')"}
 *     }
 * )
 */
class Supplier
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"suppliers_read", "provisions_read", "products_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     * @Groups({"suppliers_read", "provisions_read", "products_read"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Seller::class)
     * @Groups({"suppliers_read", "provisions_read", "products_read"})
     */
    private $seller;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"suppliers_read", "provisions_read", "products_read"})
     * @Assert\Email(message="L'adresse email saisie n'est pas valide.")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Groups({"suppliers_read", "provisions_read", "products_read"})
     * @Assert\Regex(
     *     pattern="/^(?:(?:\+|00)262|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/",
     *     match=true,
     *     message="Le numéro de téléphone saisi n'est pas valide."
     * )
     */
    private $phone;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"suppliers_read", "provisions_read", "products_read"})
     */
    private $accountingId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"suppliers_read", "provisions_read", "products_read"})
     */
    private $accountingCompanyId;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"suppliers_read", "provisions_read", "products_read"})
     */
    private $isIntern;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({"suppliers_read", "provisions_read", "products_read"})
     */
    private $vifCode;

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

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function setSeller(?Seller $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

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

    public function getAccountingCompanyId(): ?int
    {
        return $this->accountingCompanyId;
    }

    public function setAccountingCompanyId(?int $accountingCompanyId): self
    {
        $this->accountingCompanyId = $accountingCompanyId;

        return $this;
    }

    public function getIsIntern(): ?bool
    {
        return $this->isIntern;
    }

    public function setIsIntern(?bool $isIntern): self
    {
        $this->isIntern = $isIntern;

        return $this;
    }

    public function getVifCode(): ?string
    {
        return $this->vifCode;
    }

    public function setVifCode(?string $vifCode): self
    {
        $this->vifCode = $vifCode;

        return $this;
    }
}
