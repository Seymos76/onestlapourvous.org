<?php


namespace App\Appointment;


use App\Entity\Appointment;
use App\Entity\History;
use App\Entity\Patient;
use App\Entity\Therapist;
use App\Repository\AppointmentRepository;

class AppointmentAccessor
{
    private AppointmentRepository $appointmentRepository;

    public function __construct(AppointmentRepository $appointmentRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
    }

    public function getById(int $id)
    {
        return $this->appointmentRepository->find($id);
    }

    public function getBookingsByPatient(Patient $patient): array
    {
        return $this->appointmentRepository->findBy(
            ['patient' => $patient, 'status' => Appointment::STATUS_BOOKED]
        );
    }

    public function filterAppointments(array $appointsAndHistory)
    {
        return array_filter($appointsAndHistory, function ($a, $k) {
            return !$a instanceof History;
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function getByParameters(array $parameters, Therapist $therapist): array
    {
        return $this->appointmentRepository->findAvailableBookingsByParams($parameters, $therapist);
    }
}