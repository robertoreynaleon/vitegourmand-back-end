<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\MenuImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MenuImageRepository::class)]
#[ORM\Table(name: 'menu_images')]
#[ApiResource]
class MenuImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['menu:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'images')]
    #[ORM\JoinColumn(name: 'menu_id', nullable: false, onDelete: 'CASCADE')]
    // Pas de Groups ici pour éviter la référence circulaire
    private ?Menu $menu = null;

    #[ORM\Column(length: 255)]
    #[Groups(['menu:read'])]
    private ?string $imagePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['menu:read'])]
    private ?string $altText = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): static
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function setAltText(?string $altText): static
    {
        $this->altText = $altText;

        return $this;
    }
}
