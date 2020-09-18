<?php


namespace App\Appointment;


use App\Entity\Appointment;

class AppointmentConfirmation extends AbstractAppointmentManager
{
    public function confirm(Appointment $appointment)
    {
        $appointment->setBooked(true);
        $appointment->setStatus(Appointment::STATUS_BOOKED);
        $this->notify();
    }
}