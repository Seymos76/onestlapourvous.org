<?php


namespace App\User;


use App\Entity\Appointment;
use App\Entity\History;
use App\Entity\Patient;
use App\History\HistoryHelper;

class UserMalus
{
    private $historyHelper;

    public function __construct(HistoryHelper $historyHelper)
    {
        $this->historyHelper = $historyHelper;
    }

    public function manageByStatus(Appointment $appointment, Patient $patient, string $status)
    {
        if ($status === Appointment::STATUS_DISHONORED) {
            $patient->addMalus();
            $this->historyHelper->addHistoryItem(History::ACTION_DISHONORED, $appointment);
        } else {
            $this->historyHelper->addHistoryItem(History::ACTION_HONORED, $appointment);
        }
        $appointment->setStatus($status);
    }
}