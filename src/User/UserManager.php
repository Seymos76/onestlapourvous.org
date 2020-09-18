<?php


namespace App\User;


use App\Entity\Appointment;
use App\Entity\Patient;
use App\Entity\Therapist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\User\UserInterface;

class UserManager
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function deleteAccount(UserInterface $user)
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        $session = new Session();
        $session->invalidate();
    }

    public function clearAppointment(Therapist $therapist, Appointment $appointment)
    {
        $therapist->removeAppointment($appointment);
        $this->entityManager->remove($appointment);
    }
}