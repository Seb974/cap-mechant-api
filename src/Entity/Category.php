<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @ApiResource(
 *      denormalizationContext={"groups"={"category_write"}},
 *      normalizationContext={"groups"={"categories_read"}},
 *      collectionOperations={
 *          "GET",
 *          "POST"={"security"="is_granted('ROLE_SELLER')"},
 *     },
 *     itemOperations={
 *          "GET",
 *          "PUT"={"security"="is_granted('ROLE_SELLER')"},
 *          "PATCH"={"security"="is_granted('ROLE_SELLER')"},
 *          "DELETE"={"security"="is_granted('ROLE_SELLER')"}
 *     },
 *      mercure={"private": false})
 * )
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"categories_read", "products_read", "category_write", "users_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Groups({"categories_read", "products_read", "category_write", "users_read"})
     * @Assert\NotBlank(message="Un nom est obligatoire.")
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity=Group::class)
     * @Groups({"categories_read", "products_read", "category_write"})
     */
    private $userGroups;

    /**
     * @ORM\ManyToMany(targetEntity=Catalog::class)
     * @Groups({"categories_read", "products_read", "category_write"})
     */
    private $catalogs;

    /**
     * @ORM\OneToMany(targetEntity=Restriction::class, mappedBy="category", cascade={"persist", "remove"})
     * @Groups({"categories_read", "products_read", "category_write"})
     */
    private $restrictions;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"categories_read", "products_read", "category_write", "users_read"})
     */
    private $code;

    public function __construct()
    {
        $this->userGroups = new ArrayCollection();
        $this->catalogs = new ArrayCollection();
        $this->restrictions = new ArrayCollection();
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

    /**
     * @return Collection|Restriction[]
     */
    public function getRestrictions(): Collection
    {
        return $this->restrictions;
    }

    public function addRestriction(Restriction $restriction): self
    {
        if (!$this->restrictions->contains($restriction)) {
            $this->restrictions[] = $restriction;
            $restriction->setCategory($this);
        }

        return $this;
    }

    public function removeRestriction(Restriction $restriction): self
    {
        if ($this->restrictions->removeElement($restriction)) {
            // set the owning side to null (unless already changed)
            if ($restriction->getCategory() === $this) {
                $restriction->setCategory(null);
            }
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
