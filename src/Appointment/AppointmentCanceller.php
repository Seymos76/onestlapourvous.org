<?php


namespace App\Appointment;


use App\Entity\Appointment;

class AppointmentCanceller extends AbstractAppointmentManager
{
    public function cancel(Appointment $appointment)
    {
        $appointment->setBooked(false)
            ->setCancelled(true)
            ->setPatient(null)
            ->setStatus(Appointment::STATUS_AVAILABLE)
        ;
        $this->notify();
    }
}