<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['pseudo'], message: 'This pseudo is already in use')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $pseudo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    /**
     * @var Collection<int, Pop>
     */
    #[ORM\OneToMany(targetEntity: Pop::class, mappedBy: 'author')]
    private Collection $pops;

 /**
     * @var Collection<int, Group>
     */
    #[ORM\ManyToMany(targetEntity: Group::class, mappedBy: 'users')]
    private Collection $groups;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'followers')]
    #[ORM\JoinTable(name: 'user_following')]
    private Collection $following;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'following')]
    private Collection $followers;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $profilePicture;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable(); 
        $this->roles = ['ROLE_USER']; 
        $this->pops = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }

    // Getters et setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

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

    public function getRoles(): array
    {
        $roles = $this->roles;
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->pseudo;  // Utiliser le pseudo comme identifiant unique
    }

    public function eraseCredentials(): void
    {
        // Si tu stockes des données sensibles temporairement, nettoie-les ici.
    }

    public function getSalt(): ?string
    {
        return null; // Utilisation de "bcrypt" ou "argon2i" n'exige pas de sel.
    }

 /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(Group $group): static
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->addUser($this);
        }

        return $this;
    }

    public function removeGroup(Group $group): static
    {
        if ($this->groups->removeElement($group)) {
            $group->removeUser($this);
        }

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
            $pop->setAuthor($this);
        }

        return $this;
    }

    public function removePop(Pop $pop): static
    {
        if ($this->pops->removeElement($pop)) {
            // set the owning side to null (unless already changed)
            if ($pop->getAuthor() === $this) {
                $pop->setAuthor(null);
            }
        }

        return $this;
    }

    // Système de suivi (Follow)

    public function getFollowing(): Collection
    {
        return $this->following;
    }

    public function follow(User $user): void
    {
        if (!$this->following->contains($user)) {
            $this->following->add($user);
        }
    }

    public function unfollow(User $user): void
    {
        if ($this->following->contains($user)) {
            $this->following->removeElement($user);
        }
    }

    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function isFollowing(User $user): bool
{
    return $this->following->contains($user);
}

public function getProfilePicture(): ?string
{
    return $this->profilePicture;
}

public function setProfilePicture(?string $profilePicture): self
{
    $this->profilePicture = $profilePicture;

    return $this;
}
}
