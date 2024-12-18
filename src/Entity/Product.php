<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getProduct"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getProduct"])]

    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getProduct"])]

    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getProduct"])]

    private ?string $photo = null;

    #[ORM\Column]
    #[Groups(["getProduct"])]

    private ?float $price = null;

    #[ORM\ManyToMany(targetEntity: Cart::class, mappedBy: 'Products')]
    private Collection $cartId;

    public function __construct()
    {
        $this->cartId = new ArrayCollection();
    }

    // #[ORM\ManyToMany(targetEntity: Order::class, mappedBy: 'products')]
    // private Collection $orders;

    // public function __construct()
    // {
    //     $this->orders = new ArrayCollection();
    // }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    // /**
    //  * @return Collection<int, Order>
    //  */
    // public function getOrders(): Collection
    // {
    //     return $this->orders;
    // }

    // public function addOrder(Order $order): self
    // {
    //     if (!$this->orders->contains($order)) {
    //         $this->orders->add($order);
    //         $order->addProduct($this);
    //     }

    //     return $this;
    // }

    // public function removeOrder(Order $order): self
    // {
    //     if ($this->orders->removeElement($order)) {
    //         $order->removeProduct($this);
    //     }

    //     return $this;
    // }

    /**
     * @return Collection<int, Cart>
     */
    public function getCartId(): Collection
    {
        return $this->cartId;
    }

    public function addCartId(Cart $cartId): self
    {
        if (!$this->cartId->contains($cartId)) {
            $this->cartId->add($cartId);
            $cartId->addProduct($this);
        }

        return $this;
    }

    public function removeCartId(Cart $cartId): self
    {
        if ($this->cartId->removeElement($cartId)) {
            $cartId->removeProduct($this);
        }

        return $this;
    }
}
