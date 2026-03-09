<?php

namespace App\Entity;

use App\Enum\DishType;
use App\Repository\MenuDishRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Join entity for the `menu_dishes` table.
 * The composite primary key is (menu_id, dish_id).
 * The extra `dish_type` column prevents using a simple ManyToMany.
 */
#[ORM\Entity(repositoryClass: MenuDishRepository::class)]
#[ORM\Table(name: 'menu_dishes')]
class MenuDish
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'menuDishes')]
    #[ORM\JoinColumn(name: 'menu_id', nullable: false)]
    private Menu $menu;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Dish::class, inversedBy: 'menuDishes')]
    #[ORM\JoinColumn(name: 'dish_id', nullable: false)]
    private Dish $dish;

    #[ORM\Column(type: 'string', enumType: DishType::class)]
    private DishType $dishType;

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
