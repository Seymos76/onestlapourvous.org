<?php


namespace App\User;


use App\Entity\Patient;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserAccessor extends AbstractAccessor
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getUserById(int $id)
    {
        return $this->entityManager->getRepository(User::class)->find($id);
    }

    public function getPatientByEmail(string $email): Patient
    {
        return $this->entityManager->getRepository(Patient::class)->findOneBy(['email' => $email]);
    }

    public function getCurrentUser(string $email, string $className = null)
    {
        return $this->entityManager->getRepository($className)->findOneBy(['email' => $email]);
    }
}