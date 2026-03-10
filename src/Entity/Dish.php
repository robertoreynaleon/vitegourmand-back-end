<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DishRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DishRepository::class)]
#[ORM\Table(name: 'dishes')]
#[ApiResource(
    normalizationContext: ['groups' => ['dish:read']],
    denormalizationContext: ['groups' => ['dish:write']]
)]
class Dish
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['dish:read', 'menu:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Groups(['dish:read', 'menu:read', 'dish:write'])]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['dish:read', 'dish:write'])]
    private ?string $photos = null;

    #[ORM\ManyToMany(targetEntity: Allergen::class, inversedBy: 'dishes')]
    #[ORM\JoinTable(
        name: 'dish_allergens',
        joinColumns: [new ORM\JoinColumn(name: 'dish_id', referencedColumnName: 'id', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'allergen_id', referencedColumnName: 'id', onDelete: 'CASCADE')],
    )]
    #[Groups(['dish:read'])]
    private Collection $allergens;

    #[ORM\OneToMany(targetEntity: MenuDish::class, mappedBy: 'dish')]
    // Pas de Groups ici pour éviter la référence circulaire
    private Collection $menuDishes;

    public function __construct()
    {
        $this->allergens  = new ArrayCollection();
        $this->menuDishes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getPhotos(): ?string
    {
        return $this->photos;
    }

    public function setPhotos(?string $photos): static
    {
        $this->photos = $photos;

        return $this;
    }

    /**
     * @return Collection<int, Allergen>
     */
    public function getAllergens(): Collection
    {
        return $this->allergens;
    }

    public function addAllergen(Allergen $allergen): static
    {
        if (!$this->allergens->contains($allergen)) {
            $this->allergens->add($allergen);
        }

        return $this;
    }

    public function removeAllergen(Allergen $allergen): static
    {
        $this->allergens->removeElement($allergen);

        return $this;
    }

    /**
     * @return Collection<int, MenuDish>
     */
    public function getMenuDishes(): Collection
    {
        return $this->menuDishes;
    }
}
