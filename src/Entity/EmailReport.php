<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EmailReportRepository")
 */
class EmailReport
{
    public const TYPE_REGISTRATION = 'registration';
    public const TYPE_REGISTRATION_CONFIRMATION = 'registration_confirmation';
    public const TYPE_REGISTRATION_CONFIRMATION_RETRY = 'registration_confirmation_retry';
    public const TYPE_REGISTRATION_ACTIVATION = 'registration_activation';
    public const TYPE_PASSWORD_RESET_REQUEST = 'password_reset_request';
    public const TYPE_PASSWORD_RESET_SUCCESS = 'password_reset_success';
    public const TYPE_BOOKING_CONFIRMATION = 'booking_confirmation';
    public const TYPE_BOOKING_CANCELLATION_BY_PATIENT = 'booking_cancellation_by_patient';
    public const TYPE_BOOKING_CANCELLATION_BY_THERAPIST = 'booking_cancellation_by_therapist';
    public const TYPE_CONTACT_ONE_USER = 'contact_one_user';
    public const TYPE_ACCOUNT_DELETION = 'account_deletion';
    public const TYPE_CHANGE_EMAIL_ADDR = 'change_email_addr';
    public const TYPE_ALERT_DISHONORED = 'alert_dishonored';

    public const EMAIL_TYPE = [
        'registration' => "Inscription",
        'registration_confirmation' => "Confirmation inscription",
        'registration_confirmation_retry' => "Confirmation inscription renvoi",
        'registration_activation' => "Activation directe",
        'password_reset_request' => "Demande reset mot de passe",
        'password_reset_success' => "Reset mot de passe succès",
        'booking_confirmation' => "Confirmation réservation",
        'booking_cancellation_by_patient' => "Annulation réservation par demandeur.se",
        'booking_cancellation_by_therapist' => "Annulation réservation par praticien.ne",
        'contact_one_user' => "Contact utilisateur seul",
        'account_deletion' => "Suppression compte",
        'change_email_addr' => "Changement adresse email",
        'alert_dishonored' => "Avertissement 3 rdv non honorés"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $sentAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sender;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $recipient;

    /**
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * @ORM\Column(type="boolean")
     */
    private $success;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeInterface $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function setRecipient(string $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getSuccess(): ?bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
