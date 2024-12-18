<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
// use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getProduct"])]
    private ?int $id = null;

    #[Groups(["getProduct"])]
    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'cartId')]
    private Collection $Products;



    // #[ORM\ManyToOne(inversedBy: 'carts')]
    // #[ORM\JoinColumn(nullable: false)]
    // private ?User $userId = null;
    // #[Groups(["getProduct"])]
    #[ORM\ManyToOne(inversedBy: 'carts', cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userId = null;

    #[ORM\Column]
    #[Groups(["getProduct"])]
    private ?bool $confirmed = null;

    // #[Groups(["getProduct"])]
    #[ORM\OneToOne(mappedBy: 'cartId', cascade: ['persist', 'remove'])]
    private ?Order $orderId = null;


    public function __construct()
    {
        $this->Products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->Products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->Products->contains($product)) {
            $this->Products->add($product);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->Products->removeElement($product);

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function isConfirmed(): ?bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): self
    {
        $this->confirmed = $confirmed;
        return $this;
    }

    public function getOrderId(): ?Order
    {
        return $this->orderId;
    }

    public function setOrderId(Order $orderId): self
    {
        // set the owning side of the relation if necessary
        if ($orderId->getCartId() !== $this) {
            $orderId->setCartId($this);
        }

        $this->orderId = $orderId;

        return $this;
    }
}
