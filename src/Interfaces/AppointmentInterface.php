<?php


namespace App\Interfaces;


use App\Entity\Patient;
use App\Entity\Therapist;

interface AppointmentInterface
{
    public function getPatient(): ?Patient;
    public function getTherapist(): ?Therapist;
    public function getBookingDate(): ?\DateTimeInterface;
    public function getStatus(): ?string;
    public function getCancelled(): ?bool;

}