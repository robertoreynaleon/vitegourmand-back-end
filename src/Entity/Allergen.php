<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AllergenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AllergenRepository::class)]
#[ORM\Table(name: 'allergens')]
#[ApiResource(
    normalizationContext: ['groups' => ['allergen:read']],
    denormalizationContext: ['groups' => ['allergen:write']]
)]
class Allergen
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['allergen:read', 'dish:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Groups(['allergen:read', 'dish:read', 'allergen:write'])]
    private ?string $label = null;

    #[ORM\ManyToMany(targetEntity: Dish::class, mappedBy: 'allergens')]
    // Pas de Groups ici pour éviter la boucle Allergen → $dishes → Dish → $allergens → Allergen
    private Collection $dishes;

    public function __construct()
    {
        $this->dishes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, Dish>
     */
    public function getDishes(): Collection
    {
        return $this->dishes;
    }

    public function addDish(Dish $dish): static
    {
        if (!$this->dishes->contains($dish)) {
            $this->dishes->add($dish);
            $dish->addAllergen($this);
        }

        return $this;
    }

    public function removeDish(Dish $dish): static
    {
        if ($this->dishes->removeElement($dish)) {
            $dish->removeAllergen($this);
        }

        return $this;
    }
}
