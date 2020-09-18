<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PatientRepository")
 */
class Patient extends User
{
    private const ROLE_PATIENT = "ROLE_PATIENT";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isMajor;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Appointment", mappedBy="patient")
     */
    private $appointments;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $malus;

    public function __construct()
    {
        parent::__construct();
        $this->roles = ["ROLE_USER", self::ROLE_PATIENT];
        $this->appointments = new ArrayCollection();
        $this->malus = 0;
    }

    public function getId(): ?int
    {
        return parent::getId();
    }

    public function getIsMajor(): ?bool
    {
        return $this->isMajor;
    }

    public function setIsMajor(bool $isMajor): self
    {
        $this->isMajor = $isMajor;

        return $this;
    }

    /**
     * @return Collection|Appointment[]
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): self
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments[] = $appointment;
            $appointment->setPatient($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): self
    {
        if ($this->appointments->contains($appointment)) {
            $this->appointments->removeElement($appointment);
            // set the owning side to null (unless already changed)
            if ($appointment->getPatient() === $this) {
                $appointment->setPatient(null);
            }
        }

        return $this;
    }

    public function addMalus(): self
    {
        $this->malus++;
        return $this;
    }

    public function getMalus(): ?int
    {
        return $this->malus;
    }

    public function setMalus(?int $malus): self
    {
        $this->malus = $malus;

        return $this;
    }
}
