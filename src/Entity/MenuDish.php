<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\DishType;
use App\Repository\MenuDishRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MenuDishRepository::class)]
#[ORM\Table(name: 'menu_dishes')]
#[ORM\UniqueConstraint(name: 'unique_menu_dish', columns: ['menu_id', 'dish_id'])]
#[ApiResource(
    normalizationContext: ['groups' => ['menu_dish:read']],
    denormalizationContext: ['groups' => ['menu_dish:write']]
)]
class MenuDish
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['menu_dish:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'menuDishes')]
    #[ORM\JoinColumn(name: 'menu_id', nullable: false)]
    #[Groups(['menu_dish:read'])]
    // menu:read retiré ici pour éviter la boucle Menu → MenuDish.$menu(menu:read) → Menu
    private Menu $menu;

    #[ORM\ManyToOne(targetEntity: Dish::class, inversedBy: 'menuDishes')]
    #[ORM\JoinColumn(name: 'dish_id', nullable: false)]
    #[Groups(['menu_dish:read', 'menu:read'])]
    private Dish $dish;

    #[ORM\Column(type: 'string', enumType: DishType::class)]
    #[Groups(['menu_dish:read', 'menu:read'])]
    private DishType $dishType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMenu(): Menu
    {
        return $this->menu;
    }

    public function setMenu(Menu $menu): static
    {
        $this->menu = $menu;

        return $this;
    }

    public function getDish(): Dish
    {
        return $this->dish;
    }

    public function setDish(Dish $dish): static
    {
        $this->dish = $dish;

        return $this;
    }

    public function getDishType(): DishType
    {
        return $this->dishType;
    }

    public function setDishType(DishType $dishType): static
    {
        $this->dishType = $dishType;

        return $this;
    }
}
