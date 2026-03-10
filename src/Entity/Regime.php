<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\RegimeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: RegimeRepository::class)]
#[ORM\Table(name: 'regimes')]
#[ApiResource(
    normalizationContext: ['groups' => ['regime:read']],
    denormalizationContext: ['groups' => ['regime:write']]
)]
class Regime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['regime:read', 'menu:read', 'menu_dish:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Groups(['regime:read', 'menu:read', 'menu_dish:read', 'regime:write'])]
    private ?string $label = null;

    #[ORM\OneToMany(targetEntity: Menu::class, mappedBy: 'regime')]
    // Pas de Groups ici pour éviter la référence circulaire Menu → Regime → Menu
    private Collection $menus;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
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
     * @return Collection<int, Menu>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): static
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->setRegime($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): static
    {
        if ($this->menus->removeElement($menu)) {
            if ($menu->getRegime() === $this) {
                $menu->setRegime(null);
            }
        }

        return $this;
    }
}
