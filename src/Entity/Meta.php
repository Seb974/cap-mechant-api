<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\MetaRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=MetaRepository::class)
 * @ApiResource(
 *     attributes={"force_eager"=false},
 *     denormalizationContext={"disable_type_enforcement"=true},
 *     normalizationContext={
 *          "groups"={"metas_read", "users_read"}
 *     },
 *     collectionOperations={
 *          "GET"={"security"="is_granted('ROLE_PICKER') or is_granted('ROLE_SUPERVISOR')"},
 *          "POST"
 *     },
 *     itemOperations={
 *          "GET"={"security"="is_granted('ROLE_PICKER') or is_granted('ROLE_SUPERVISOR') or object.getUser() == user"},
 *          "PUT"={"security"="is_granted('ROLE_PICKER') or is_granted('ROLE_SUPERVISOR') or object.getUser() == user"},
 *          "PATCH"={"security"="is_granted('ROLE_PICKER') or is_granted('ROLE_SUPERVISOR') or object.getUser() == user"},
 *          "DELETE"={"security"="is_granted('ROLE_PICKER') or is_granted('ROLE_SUPERVISOR') or object.getUser() == user"}
 *     },
 *     mercure="object.getMercureOptions(object.getUser())"
 * )
 */
class Meta
{
    /**
     * server domain, used to configure the Mercure hub topics
     */
    private static $domain = 'https://api.cap-mechant.re';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"metas_read", "users_read", "user_write", "relaypoints_read", "relaypoint_write", "orders_read", "order_write", "tourings_read", "platform_write", "platforms_read", "supervisors_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"metas_read", "users_read", "user_write", "relaypoints_read", "relaypoint_write", "orders_read", "order_write", "tourings_read", "platform_write", "platforms_read", "supervisors_read"})
     * @Assert\NotBlank(message="Une adresse est obligatoire.")
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"metas_read", "users_read", "user_write", "relaypoints_read", "relaypoint_write", "orders_read", "order_write", "tourings_read", "platform_write", "platforms_read", "supervisors_read"})
     */
    private $address2;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({"metas_read", "users_read", "user_write", "relaypoints_read", "relaypoint_write", "orders_read", "order_write", "tourings_read", "platform_write", "platforms_read", "supervisors_read"})
     * @Assert\Regex(
     *     pattern="/^(?:[0-9]\d|9[0-8])\d{3}$/",
     *     match=true,
     *     message="Le code postal saisi n'est pas valide."
     * )
     */
    private $zipcode;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"metas_read", "users_read", "user_write", "relaypoints_read", "relaypoint_write", "orders_read", "order_write", "tourings_read", "platform_write", "platforms_read", "supervisors_read"})
     */
    private $city;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"metas_read", "users_read", "user_write", "relaypoints_read", "relaypoint_write", "orders_read", "order_write", "tourings_read", "platform_write", "platforms_read", "supervisors_read"})
     */
    private $position = [];

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     *  @Groups({"metas_read", "users_read", "user_write", "relaypoints_read", "relaypoint_write", "orders_read", "order_write", "tourings_read", "platform_write", "platforms_read", "supervisors_read"})
     * @Assert\Regex(
     *     pattern="/^(?:(?:\+|00)262|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/",
     *     match=true,
     *     message="Le numéro de téléphone saisi n'est pas valide."
     * )
     */
    private $phone;

    // cascade={"persist"}
    /**
     * @ORM\OneToOne(targetEntity=User::class, mappedBy="metas")
     * @Groups({"metas_read"})
     */
    private $user;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"metas_read", "users_read", "user_write", "relaypoints_read", "relaypoint_write", "orders_read", "order_write", "tourings_read", "platform_write", "platforms_read"})
     */
    private $isRelaypoint;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): self
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode): self
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPosition(): ?array
    {
        return $this->position;
    }

    public function setPosition(?array $position): self
    {
        $this->position = $position;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setMetas(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getMetas() !== $this) {
            $user->setMetas($this);
        }

        $this->user = $user;

        return $this;
    }

    public function getMercureOptions($user): array
    {
        return $user == null ? ["private" => false] : ["private" => true, 
                "topics" => self::$domain . "/api/users/" . $user->getId() . "/metas",
                "normalization_context" => [ "group" => "users_read"]
        ];
    }

    public function getIsRelaypoint(): ?bool
    {
        return $this->isRelaypoint;
    }

    public function setIsRelaypoint(?bool $isRelaypoint): self
    {
        $this->isRelaypoint = $isRelaypoint;

        return $this;
    }
}
