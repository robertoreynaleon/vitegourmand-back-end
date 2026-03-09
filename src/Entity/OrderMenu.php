<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\OrderMenuRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderMenuRepository::class)]
#[ORM\Table(name: 'order_menus')]
#[ApiResource]
class OrderMenu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'orderMenus')]
    #[ORM\JoinColumn(name: 'order_id', nullable: false, onDelete: 'CASCADE')]
    private ?Order $order = null;

    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'orderMenus')]
    #[ORM\JoinColumn(name: 'menu_id', nullable: false)]
    private ?Menu $menu = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $pricePerPerson = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPricePerPerson(): ?string
    {
        return $this->pricePerPerson;
    }

    public function setPricePerPerson(string $pricePerPerson): static
    {
        $this->pricePerPerson = $pricePerPerson;

        return $this;
    }
}
