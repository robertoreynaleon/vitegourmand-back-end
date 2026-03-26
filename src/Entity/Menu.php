<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
#[ORM\Table(name: 'menus')]
#[ApiResource(
    normalizationContext: ['groups' => ['menu:read']],
    denormalizationContext: ['groups' => ['menu:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['regime.id' => 'exact'])]
#[ApiFilter(RangeFilter::class, properties: ['pricePerPerson', 'minPeople'])]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['menu:read', 'menu_dish:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Groups(['menu:read', 'menu:write', 'menu_dish:read'])]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['menu:read', 'menu:write'])]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Regime::class, inversedBy: 'menus')]
    #[ORM\JoinColumn(name: 'regime_id', nullable: false)]
    #[Groups(['menu:read', 'menu:write'])]
    private ?Regime $regime = null;

    #[ORM\Column(type: 'decimal', precision: 6, scale: 2)]
    #[Groups(['menu:read', 'menu:write'])]
    private ?string $pricePerPerson = null;

    #[ORM\Column]
    #[Groups(['menu:read', 'menu:write'])]
    private int $minPeople = 6;

    #[ORM\Column(nullable: true)]
    #[Groups(['menu:read', 'menu:write'])]
    private ?int $remainingQuantity = 0;

    #[ORM\Column]
    #[Groups(['menu:read', 'menu:write'])]
    private int $advanceOrderDays = 2;

    #[ORM\OneToMany(targetEntity: MenuImage::class, mappedBy: 'menu', cascade: ['persist', 'remove'])]
    #[Groups(['menu:read'])]
    private Collection $images;

    #[ORM\OneToMany(targetEntity: MenuDish::class, mappedBy: 'menu', cascade: ['persist', 'remove'])]
    #[Groups(['menu:read'])]
    private Collection $menuDishes;

    #[ORM\OneToMany(targetEntity: OrderMenu::class, mappedBy: 'menu')]
    private Collection $orderMenus;

    public function __construct()
    {
        $this->images     = new ArrayCollection();
        $this->menuDishes = new ArrayCollection();
        $this->orderMenus = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getRegime(): ?Regime
    {
        return $this->regime;
    }

    public function setRegime(?Regime $regime): static
    {
        $this->regime = $regime;

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

    public function getMinPeople(): int
    {
        return $this->minPeople;
    }

    public function setMinPeople(int $minPeople): static
    {
        $this->minPeople = $minPeople;

        return $this;
    }

    public function getRemainingQuantity(): ?int
    {
        return $this->remainingQuantity;
    }

    public function setRemainingQuantity(?int $remainingQuantity): static
    {
        $this->remainingQuantity = $remainingQuantity;

        return $this;
    }

    public function getAdvanceOrderDays(): int
    {
        return $this->advanceOrderDays;
    }

    public function setAdvanceOrderDays(int $advanceOrderDays): static
    {
        $this->advanceOrderDays = $advanceOrderDays;

        return $this;
    }

    /**
     * @return Collection<int, MenuImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(MenuImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setMenu($this);
        }

        return $this;
    }

    public function removeImage(MenuImage $image): static
    {
        if ($this->images->removeElement($image)) {
            if ($image->getMenu() === $this) {
                $image->setMenu(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MenuDish>
     */
    public function getMenuDishes(): Collection
    {
        return $this->menuDishes;
    }

    /**
     * @return Collection<int, OrderMenu>
     */
    public function getOrderMenus(): Collection
    {
        return $this->orderMenus;
    }
}
