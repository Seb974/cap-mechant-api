<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use App\Filter\UserFilterByRolesFilter;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Filter\UserFilterByNameAndEmailFilter;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(
 *     fields={"email"},
 *     message="Cette adresse e-mail est déjà associée à un compte."
 * )
 * @ApiResource(
 *     denormalizationContext={
 *          "disable_type_enforcement"=true,
 *          "groups"={"user_write"}
 *     },
 *     normalizationContext={
 *          "groups"={"users_read"},
 *          "enable_max_depth"=true
 *     },
 *     collectionOperations={
 *          "GET"={"security"="is_granted('ROLE_TEAM')"},
 *          "POST"
 *     },
 *     itemOperations={
 *          "GET"={"security"="is_granted('ROLE_TEAM') or object == user"},
 *          "PUT"={"security"="is_granted('ROLE_TEAM') or object == user"},
 *          "PATCH"={"security"="is_granted('ROLE_TEAM') or object == user"},
 *          "DELETE"={"security"="is_granted('ROLE_TEAM') or object == user"}
 *     },
 *     mercure={"private"=true, "normalization_context"={"group"="users_read"}},
 * )
 * @ApiFilter(UserFilterByNameAndEmailFilter::class, properties={"name"="partial", "email"="partial"})
 * @ApiFilter(UserFilterByRolesFilter::class, properties={"roles"="partial"})
 * @ApiFilter(BooleanFilter::class, properties={"isIntern"})
 * @ApiFilter(OrderFilter::class, properties={"name"})
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"users_read", "user_write", "orders_read", "sellers_read", "deliverers_read", "seller:products_read", "tourings_read", "platforms_read", "supervisors_read", "relaypoints_read", "provisions_read", "admin:orders_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"users_read", "user_write", "orders_read", "sellers_read", "deliverers_read", "tourings_read", "platforms_read", "supervisors_read", "relaypoints_read", "provisions_read", "admin:orders_read"})
     * @Assert\Email(message="L'adresse email saisie n'est pas valide.")
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"users_read", "user_write", "supervisors_read", "admin:orders_read"})
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\Length(min = 6, minMessage = "Le mot de passe doit contenir au moins {{ limit }} caractères.")
     * @Groups({"user_write"})
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"users_read", "user_write", "orders_read", "sellers_read", "deliverers_read", "tourings_read", "platforms_read", "supervisors_read", "relaypoints_read", "provisions_read", "admin:orders_read"})
     * @Assert\Length(min = 3, minMessage = "Le nom doit contenir au moins {{ limit }} caractères.",
     *                max = 100, maxMessage= "Le nom ne peut dépasser {{ limit }} caractères.")
     */
    private $name;

    // , "provisions_read"
    /**
     * @ORM\OneToOne(targetEntity=Meta::class, inversedBy="user", cascade={"persist", "remove"})
     * @Groups({"users_read", "user_write", "supervisors_read", "admin:orders_read"})
     */
    private $metas;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"users_read", "user_write", "orders_read", "order_write", "admin:orders_read"})
     */
    private $orderCount;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"users_read", "user_write", "orders_read", "order_write", "admin:orders_read"})
     */
    private $lastOrder;

    /**
     * @ORM\OneToOne(targetEntity=Supervisor::class, mappedBy="supervisor", cascade={"persist", "remove"})
     * @Groups({"users_read", "user_write"})
     * @MaxDepth(2)
     */
    private $supervisorAuthority;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"users_read", "user_write", "supervisors_read", "admin:orders_read"})
     */
    private $accountingId;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"users_read", "user_write", "supervisors_read", "admin:orders_read"})
     */
    private $billingDetails;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"users_read", "user_write", "supervisors_read", "admin:orders_read", "provisions_read"})
     */
    private $vifCode;

    /**
     * @ORM\ManyToMany(targetEntity=Product::class, inversedBy="users")
     * @Groups({"users_read", "user_write"})
     */
    private $products;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"users_read", "user_write", "supervisors_read", "admin:orders_read", "provisions_read", "orders_read", "order_write"})
     */
    private $isIntern;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getMetas(): ?Meta
    {
        return $this->metas;
    }

    public function setMetas(?Meta $metas): self
    {
        $this->metas = $metas;

        return $this;
    }

    public function getOrderCount(): ?int
    {
        return $this->orderCount;
    }

    public function setOrderCount(?int $orderCount): self
    {
        $this->orderCount = $orderCount;

        return $this;
    }

    public function getLastOrder(): ?\DateTimeInterface
    {
        return $this->lastOrder;
    }

    public function setLastOrder(?\DateTimeInterface $lastOrder): self
    {
        $this->lastOrder = $lastOrder;

        return $this;
    }

    public function getSupervisorAuthority(): ?Supervisor
    {
        return $this->supervisorAuthority;
    }

    public function setSupervisorAuthority(?Supervisor $supervisorAuthority): self
    {
        // unset the owning side of the relation if necessary
        if ($supervisorAuthority === null && $this->supervisorAuthority !== null) {
            $this->supervisorAuthority->setSupervisor(null);
        }

        // set the owning side of the relation if necessary
        if ($supervisorAuthority !== null && $supervisorAuthority->getSupervisor() !== $this) {
            $supervisorAuthority->setSupervisor($this);
        }

        $this->supervisorAuthority = $supervisorAuthority;

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

    public function getBillingDetails(): ?bool
    {
        return $this->billingDetails;
    }

    public function setBillingDetails(?bool $billingDetails): self
    {
        $this->billingDetails = $billingDetails;

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

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->products->removeElement($product);

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
}
