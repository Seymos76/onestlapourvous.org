<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DepartmentRepository")
 */
class Department
{
    const COUNTRIES = [
        "fr" => "France",
        "be" => "Belgique",
        "lu" => "Luxembourg",
        "ch" => "Suisse"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"user_search", "get_bookings"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get_bookings"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $country;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Town", mappedBy="department")
     */
    private $towns;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="department")
     */
    private $users;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    public function __construct()
    {
        $this->towns = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection|Town[]
     */
    public function getTowns(): Collection
    {
        return $this->towns;
    }

    public function addTown(Town $town): self
    {
        if (!$this->towns->contains($town)) {
            $this->towns[] = $town;
            $town->setDepartment($this);
        }

        return $this;
    }

    public function removeTown(Town $town): self
    {
        if ($this->towns->contains($town)) {
            $this->towns->removeElement($town);
            // set the owning side to null (unless already changed)
            if ($town->getDepartment() === $this) {
                $town->setDepartment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setDepartment($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getDepartment() === $this) {
                $user->setDepartment(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
