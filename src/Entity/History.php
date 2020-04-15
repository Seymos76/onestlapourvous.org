<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HistoryRepository")
 */
class History extends Appointment
{
    public const ACTION_BOOKED = 'booked';
    public const ACTION_HONORED = 'honored';
    public const ACTION_DISHONORED = 'dishonored';
    public const ACTION_NEVER_BOOKED = 'never_booked';
    public const ACTION_CANCELLED_BY_THERAPIST = 'cancelled_by_therapist';
    public const ACTION_DELETED_BY_THERAPIST = 'deleted_by_therapist';
    public const ACTION_CANCELLED_BY_PATIENT = 'cancelled_by_patient';

    public const ACTIONS = [
        self::ACTION_BOOKED => "Réservé",
        self::ACTION_HONORED => "Honoré",
        self::ACTION_DISHONORED => "Non honoré",
        self::ACTION_NEVER_BOOKED => "Jamais réservé",
        self::ACTION_CANCELLED_BY_THERAPIST => "Annulée par le praticien",
        self::ACTION_DELETED_BY_THERAPIST => "Supprimé par le praticien",
        self::ACTION_CANCELLED_BY_PATIENT => "Annulée par le demandeur"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $action;

    /**
     * @ORM\Column(type="datetime")
     */
    private $actionedAt;

    public function __construct()
    {
        parent::__construct();
        $this->actionedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getActionedAt(): ?\DateTimeInterface
    {
        return $this->actionedAt;
    }

    public function setActionedAt(\DateTimeInterface $actionedAt): self
    {
        $this->actionedAt = $actionedAt;

        return $this;
    }
}
