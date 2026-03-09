<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DishRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DishRepository::class)]
#[ORM\Table(name: 'dishes')]
#[ApiResource]
class Dish
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $photos = null;

    /**
     * Owning side of the ManyToMany — maps to the `dish_allergens` join table.
     *
     * @var Collection<int, Allergen>
     */
    #[ORM\ManyToMany(targetEntity: Allergen::class, inversedBy: 'dishes')]
    #[ORM\JoinTable(
        name: 'dish_allergens',
        joinColumns: [new ORM\JoinColumn(name: 'dish_id', referencedColumnName: 'id', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'allergen_id', referencedColumnName: 'id', onDelete: 'CASCADE')],
    )]
    private Collection $allergens;

    #[ORM\OneToMany(targetEntity: MenuDish::class, mappedBy: 'dish')]
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
