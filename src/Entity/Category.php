<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    /**
     * @var Collection<int, Package>
     */
    #[ORM\OneToMany(targetEntity: Package::class, mappedBy: 'category')]
    private Collection $packages;

    /**
     * @var Collection<int, Consumer>
     */
    #[ORM\ManyToMany(targetEntity: Consumer::class, mappedBy: 'preffered_categories')]
    private Collection $consumers;

    public function __construct()
    {
        $this->packages = new ArrayCollection();
        $this->consumers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Package>
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }

    public function addPackage(Package $package): static
    {
        if (!$this->packages->contains($package)) {
            $this->packages->add($package);
            $package->setCategory($this);
        }

        return $this;
    }

    public function removePackage(Package $package): static
    {
        if ($this->packages->removeElement($package)) {
            // set the owning side to null (unless already changed)
            if ($package->getCategory() === $this) {
                $package->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Consumer>
     */
    public function getConsumers(): Collection
    {
        return $this->consumers;
    }

    public function addConsumer(Consumer $consumer): static
    {
        if (!$this->consumers->contains($consumer)) {
            $this->consumers->add($consumer);
            $consumer->addPrefferedCategory($this);
        }

        return $this;
    }

    public function removeConsumer(Consumer $consumer): static
    {
        if ($this->consumers->removeElement($consumer)) {
            $consumer->removePrefferedCategory($this);
        }

        return $this;
    }
}
