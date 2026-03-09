<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
#[ApiResource]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'user_id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $orderDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $deliveryDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $deliveryTime = null;

    #[ORM\Column(type: 'text')]
    private ?string $deliveryAddress = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $subtotal = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $deliveryFee = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $totalAmount = null;

    #[ORM\Column]
    private bool $equipmentLoan = false;

    #[ORM\Column]
    private bool $equipmentReturned = false;

    #[ORM\Column(type: 'string', enumType: OrderStatus::class)]
    private OrderStatus $status = OrderStatus::EnAttente;

    #[ORM\OneToMany(targetEntity: OrderMenu::class, mappedBy: 'order', cascade: ['persist', 'remove'])]
    private Collection $orderMenus;

    public function __construct()
    {
        $this->orderDate  = new \DateTime();
        $this->orderMenus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getOrderDate(): ?\DateTimeInterface
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTimeInterface $orderDate): static
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    public function getDeliveryDate(): ?\DateTimeInterface
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(\DateTimeInterface $deliveryDate): static
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getDeliveryTime(): ?\DateTimeInterface
    {
        return $this->deliveryTime;
    }

    public function setDeliveryTime(\DateTimeInterface $deliveryTime): static
    {
        $this->deliveryTime = $deliveryTime;

        return $this;
    }

    public function getDeliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(string $deliveryAddress): static
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function getSubtotal(): ?string
    {
        return $this->subtotal;
    }

    public function setSubtotal(string $subtotal): static
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getDeliveryFee(): ?string
    {
        return $this->deliveryFee;
    }

    public function setDeliveryFee(string $deliveryFee): static
    {
        $this->deliveryFee = $deliveryFee;

        return $this;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function isEquipmentLoan(): bool
    {
        return $this->equipmentLoan;
    }

    public function setEquipmentLoan(bool $equipmentLoan): static
    {
        $this->equipmentLoan = $equipmentLoan;

        return $this;
    }

    public function isEquipmentReturned(): bool
    {
        return $this->equipmentReturned;
    }

    public function setEquipmentReturned(bool $equipmentReturned): static
    {
        $this->equipmentReturned = $equipmentReturned;

        return $this;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function setStatus(OrderStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, OrderMenu>
     */
    public function getOrderMenus(): Collection
    {
        return $this->orderMenus;
    }

    public function addOrderMenu(OrderMenu $orderMenu): static
    {
        if (!$this->orderMenus->contains($orderMenu)) {
            $this->orderMenus->add($orderMenu);
            $orderMenu->setOrder($this);
        }

        return $this;
    }

    public function removeOrderMenu(OrderMenu $orderMenu): static
    {
        if ($this->orderMenus->removeElement($orderMenu)) {
            if ($orderMenu->getOrder() === $this) {
                $orderMenu->setOrder(null);
            }
        }

        return $this;
    }
}
