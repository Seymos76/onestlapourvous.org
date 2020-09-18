<?php

namespace App\Entity;

use App\Interfaces\TherapistInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TherapistRepository")
 */
class Therapist extends User implements TherapistInterface
{
    private const ROLE_THERAPIST = "ROLE_THERAPIST";
    private const ROLE_MANAGER = "ROLE_MANAGER";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"create_booking", "patient_research"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    private $ethicEntityCodeLabel;

    /**
     * @ORM\Column(type="string")
     */
    private $schoolEntityLabel;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $hasCertification;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isSupervised;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isRespectingEthicalFrameWork;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Appointment", mappedBy="therapist", cascade={"persist","remove"})
     */
    private $appointments;

    public function __construct()
    {
        parent::__construct();
        $this->roles = ["ROLE_USER", self::ROLE_THERAPIST];
        $this->appointments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return parent::getId();
    }

    public function getEthicEntityCodeLabel(): ?string
    {
        return $this->ethicEntityCodeLabel;
    }

    public function setEthicEntityCodeLabel(string $ethicEntityCodeLabel): void
    {
        $this->ethicEntityCodeLabel = $ethicEntityCodeLabel;
    }

    public function getSchoolEntityLabel(): ?string
    {
        return $this->schoolEntityLabel;
    }

    public function setSchoolEntityLabel(string $schoolEntityLabel): void
    {
        $this->schoolEntityLabel = $schoolEntityLabel;
    }

    public function getHasCertification(): ?bool
    {
        return $this->hasCertification;
    }

    public function isOwningCertification(): ?bool
    {
        return $this->hasCertification;
    }

    public function setHasCertification(bool $hasCertification): void
    {
        $this->hasCertification = $hasCertification;
    }

    public function getIsSupervised(): ?bool
    {
        return $this->isSupervised;
    }

    public function isSupervised(): ?bool
    {
        return $this->isSupervised;
    }

    public function setIsSupervised(bool $isSupervised): void
    {
        $this->isSupervised = $isSupervised;
    }

    public function getIsRespectingEthicalFrameWork(): ?bool
    {
        return $this->isRespectingEthicalFrameWork;
    }

    public function setIsRespectingEthicalFrameWork(bool $isRespectingEthicalFrameWork): void
    {
        $this->isRespectingEthicalFrameWork = $isRespectingEthicalFrameWork;
    }

    public function isRespectingEthicalFrameWork(): ?bool
    {
        return $this->isRespectingEthicalFrameWork;
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
            $appointment->setTherapist($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): self
    {
        if ($this->appointments->contains($appointment)) {
            $this->appointments->removeElement($appointment);
            // set the owning side to null (unless already changed)
            if ($appointment->getTherapist() === $this) {
                $appointment->setTherapist(null);
            }
        }

        return $this;
    }

    public function upgradeToManager()
    {
        $this->roles[] = self::ROLE_MANAGER;
    }
}
