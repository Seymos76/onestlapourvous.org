<?php


namespace App\Appointment;


use App\Entity\Appointment;
use App\Entity\Patient;

class AppointmentManager extends AbstractAppointmentManager
{
    private Appointment $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function book(Appointment $appointment, Patient $user)
    {
        $appointment
            ->setPatient($user)
            ->setStatus(Appointment::STATUS_BOOKING)
        ;
    }

    public function cancel(Appointment $appointment)
    {
        $appointment->setBooked(false);
        $appointment->setStatus(Appointment::STATUS_CANCELLED);
        $appointment->setPatient(null);
    }
}