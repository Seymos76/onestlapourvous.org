<?php

namespace App\Entity;

use App\Interfaces\AppointmentInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AppointmentRepository")
 */
class Appointment implements AppointmentInterface
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_BOOKING = 'booking';
    public const STATUS_BOOKED = 'booked';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_HONORED = 'honored';
    public const STATUS_DISHONORED = 'dishonored';
    public const STATUS_TO_DELETE = 'to_delete';

    public const STATUS = [
        self::STATUS_AVAILABLE => "Disponible",
        self::STATUS_BOOKING => "Réservation en cours",
        self::STATUS_BOOKED => "Réservé",
        self::STATUS_CANCELLED => "Annulé",
        self::STATUS_HONORED => "Honoré",
        self::STATUS_DISHONORED => "Non honoré",
        self::STATUS_TO_DELETE => "En attente de suppression automatique"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"create_booking", "get_bookings"})
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $booked;

    /**
     * @ORM\Column(type="date")
     * @Groups({"create_booking", "get_bookings"})
     */
    protected $bookingDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Therapist", inversedBy="appointments")
     * @Groups({"create_booking", "get_bookings"})
     */
    protected $therapist;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Patient", inversedBy="appointments")
     */
    protected $patient;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $location;

    /**
     * @ORM\Column(type="time")
     * @Groups({"create_booking", "get_bookings"})
     */
    protected $bookingStart;

    /**
     * @ORM\Column(type="time")
     * @Groups({"create_booking", "get_bookings"})
     */
    protected $bookingEnd;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $cancelled;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $cancelMessage;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $status;

    public function __construct()
    {
        $this->booked = false;
        $this->setStatus(self::STATUS_AVAILABLE);
        $this->cancelled = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooked(): ?bool
    {
        return $this->booked;
    }

    public function setBooked(bool $booked): self
    {
        $this->booked = $booked;

        return $this;
    }

    public function getBookingDate(): ?\DateTimeInterface
    {
        return $this->bookingDate;
    }

    public function setBookingDate(\DateTimeInterface $bookingDate): self
    {
        $this->bookingDate = $bookingDate;

        return $this;
    }

    public function getTherapist(): ?Therapist
    {
        return $this->therapist;
    }

    public function setTherapist(?Therapist $therapist): self
    {
        $this->therapist = $therapist;

        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getBookingStart(): ?\DateTimeInterface
    {
        return $this->bookingStart;
    }

    public function setBookingStart(\DateTimeInterface $bookingStart): self
    {
        $this->bookingStart = $bookingStart;

        return $this;
    }

    public function getBookingEnd(): ?\DateTimeInterface
    {
        return $this->bookingEnd;
    }

    public function setBookingEnd(\DateTimeInterface $bookingEnd): self
    {
        $this->bookingEnd = $bookingEnd;

        return $this;
    }

    public function getCancelled(): ?bool
    {
        return $this->cancelled;
    }

    public function setCancelled(bool $cancelled): self
    {
        $this->cancelled = $cancelled;

        return $this;
    }

    public function getCancelMessage(): ?string
    {
        return $this->cancelMessage;
    }

    public function setCancelMessage(?string $cancelMessage): self
    {
        $this->cancelMessage = $cancelMessage;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
