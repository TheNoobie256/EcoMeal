<?php

namespace App\Entity;

use App\Repository\ConsumerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsumerRepository::class)]
class Consumer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $first_name = null;

    #[ORM\Column(length: 50)]
    private ?string $last_name = null;

    #[ORM\Column(length: 20)]
    private ?string $phone_number = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'consumer', orphanRemoval: true)]
    private Collection $orders;

    #[ORM\OneToOne(mappedBy: 'consumer', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'consumers')]
    private Collection $preffered_categories;

    /**
     * @var Collection<int, Business>
     */
    #[ORM\ManyToMany(targetEntity: Business::class, inversedBy: 'consumers')]
    private Collection $favorite_businesses;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->preffered_categories = new ArrayCollection();
        $this->favorite_businesses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(string $phone_number): static
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setConsumer($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            if ($order->getConsumer() === $this) {
                $order->setConsumer(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        if ($user === null && $this->user !== null) {
            $this->user->setConsumer(null);
        }

        if ($user !== null && $user->getConsumer() !== $this) {
            $user->setConsumer($this);
        }

        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getPreferredCategories(): Collection
    {
        return $this->preffered_categories;
    }

    public function addPreferredCategory(Category $prefferedCategory): static
    {
        if (!$this->preffered_categories->contains($prefferedCategory)) {
            $this->preffered_categories->add($prefferedCategory);
        }

        return $this;
    }

    public function removePrefferedCategory(Category $prefferedCategory): static
    {
        $this->preffered_categories->removeElement($prefferedCategory);

        return $this;
    }

    /**
     * @return Collection<int, Business>
     */
    public function getFavoriteBusinesses(): Collection
    {
        return $this->favorite_businesses;
    }

    public function addFavoriteBusiness(Business $favoriteBusiness): static
    {
        if (!$this->favorite_businesses->contains($favoriteBusiness)) {
            $this->favorite_businesses->add($favoriteBusiness);
        }

        return $this;
    }

    public function removeFavoriteBusiness(Business $favoriteBusiness): static
    {
        $this->favorite_businesses->removeElement($favoriteBusiness);

        return $this;
    }
}
