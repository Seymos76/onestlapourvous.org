<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsersHistoryRepository")
 */
class UsersHistory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $patientId;

    /**
     * @ORM\Column(type="integer")
     */
    private $therapistId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $patientFirstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $patientLastName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $therapistFirstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $therapistLastName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\History", mappedBy="usersHistory")
     */
    private $histories;

    /**
     * @ORM\Column(type="integer")
     */
    private $patientMalus;

    public function __construct()
    {
        $this->histories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatientId(): ?int
    {
        return $this->patientId;
    }

    public function setPatientId(int $patientId): self
    {
        $this->patientId = $patientId;

        return $this;
    }

    public function getTherapistId(): ?int
    {
        return $this->therapistId;
    }

    public function setTherapistId(int $therapistId): self
    {
        $this->therapistId = $therapistId;

        return $this;
    }

    public function getPatientFirstName(): ?string
    {
        return $this->patientFirstName;
    }

    public function setPatientFirstName(?string $patientFirstName): self
    {
        $this->patientFirstName = $patientFirstName;

        return $this;
    }

    public function getPatientLastName(): ?string
    {
        return $this->patientLastName;
    }

    public function setPatientLastName(?string $patientLastName): self
    {
        $this->patientLastName = $patientLastName;

        return $this;
    }

    public function getTherapistFirstName(): ?string
    {
        return $this->therapistFirstName;
    }

    public function setTherapistFirstName(string $therapistFirstName): self
    {
        $this->therapistFirstName = $therapistFirstName;

        return $this;
    }

    public function getTherapistLastName(): ?string
    {
        return $this->therapistLastName;
    }

    public function setTherapistLastName(string $therapistLastName): self
    {
        $this->therapistLastName = $therapistLastName;

        return $this;
    }

    /**
     * @return Collection|History[]
     */
    public function getHistories(): Collection
    {
        return $this->histories;
    }

    public function addHistory(History $history): self
    {
        if (!$this->histories->contains($history)) {
            $this->histories[] = $history;
            $history->setUsersHistory($this);
        }

        return $this;
    }

    public function removeHistory(History $history): self
    {
        if ($this->histories->contains($history)) {
            $this->histories->removeElement($history);
            // set the owning side to null (unless already changed)
            if ($history->getUsersHistory() === $this) {
                $history->setUsersHistory(null);
            }
        }

        return $this;
    }

    public function getPatientMalus(): ?int
    {
        return $this->patientMalus;
    }

    public function setPatientMalus(int $patientMalus): self
    {
        $this->patientMalus = $patientMalus;

        return $this;
    }
}
