<?php


namespace App\History;


use App\Entity\Appointment;
use App\Entity\BookingHistory;
use App\Entity\History;
use App\Entity\Patient;
use App\Entity\Therapist;
use App\Entity\UsersHistory;
use Doctrine\ORM\EntityManagerInterface;

class HistoryHelper
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addHistoryItem(string $action, Appointment $appointment = null): History
    {
        $history = new History();
        $history->setAction($action);
        if ($action !== History::ACTION_DELETED_BY_THERAPIST && null !== $appointment) {
            $bookingHistory = $this->createBookingHistory($appointment);
            $bookingHistory->addHistory($history);
            $this->entityManager->persist($bookingHistory);
        }
        if (null !== $appointment) {
            if (!$appointment->getPatient() instanceof Patient) {
                $userHistory = $this->createUsersHistory($appointment->getTherapist(), null);
            } else {
                $userHistory = $this->createUsersHistory($appointment->getTherapist(), $appointment->getPatient());
            }
            $history->setUsersHistory($userHistory);
        }
        $this->entityManager->persist($history);
        return $history;
    }

    private function createBookingHistory(Appointment $appointment): BookingHistory
    {
        $bookingHistory = new BookingHistory();
        $bookingHistory->setBookingDate($appointment->getBookingDate());
        $bookingHistory->setBookingStart($appointment->getBookingStart());
        $bookingHistory->setStatus($appointment->getStatus());
        return $bookingHistory;
    }

    private function createUsersHistory(Therapist $therapist, ?Patient $patient = null): UsersHistory
    {
        $usersHistory = new UsersHistory();
        $usersHistory->setTherapistId($therapist->getId());
        $usersHistory->setTherapistFirstName($therapist->getFirstName());
        $usersHistory->setTherapistLastName($therapist->getLastName());
        if ($patient instanceof Patient) {
            $usersHistory->setPatientId($patient->getId());
            $usersHistory->setPatientFirstName($patient->getFirstName());
            $usersHistory->setPatientLastName($patient->getLastName());
            $usersHistory->setPatientMalus($patient->getMalus());
        }
        return $usersHistory;
    }
}