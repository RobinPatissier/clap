<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'groups')]
    private Collection $users;

    /**
     * @var Collection<int, Pop>
     */
    #[ORM\OneToMany(targetEntity: Pop::class, mappedBy: 'relatedGroup')]
    private Collection $pops;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->pops = new ArrayCollection();
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);

        return $this;
    }

    /**
     * @return Collection<int, Pop>
     */
    public function getPops(): Collection
    {
        return $this->pops;
    }

    public function addPop(Pop $pop): static
    {
        if (!$this->pops->contains($pop)) {
            $this->pops->add($pop);
            $pop->setRelatedGroup($this);
        }

        return $this;
    }

    public function removePop(Pop $pop): static
    {
        if ($this->pops->removeElement($pop)) {
            // set the owning side to null (unless already changed)
            if ($pop->getRelatedGroup() === $this) {
                $pop->setRelatedGroup(null);
            }
        }

        return $this;
    }
}
