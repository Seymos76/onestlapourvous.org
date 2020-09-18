<?php


namespace App\Services;


use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use App\Repository\PatientRepository;
use App\Repository\TherapistRepository;

trait StatisticTrait
{
    private $therapistRepository;
    private $patientRepository;
    private $appointmentRepository;

    public function __construct(
        TherapistRepository $therapistRepository,
        PatientRepository $patientRepository,
        AppointmentRepository $appointmentRepository
    )
    {
        $this->therapistRepository = $therapistRepository;
        $this->patientRepository = $patientRepository;
        $this->appointmentRepository = $appointmentRepository;
    }

    public function getFunFacts(): array
    {
        $happyHelped = sizeof($this->patientRepository->findHelped());
        $successMissions = sizeof($this->appointmentRepository->findBy(['status' => Appointment::STATUS_HONORED]));
        $volunteers = sizeof($this->therapistRepository->findBy(['isActive' => true]));
        return [
            'happy_helped' => $happyHelped,
            'success_missions' => $successMissions,
            'volunteer_reached' => $volunteers,
        ];
    }
}