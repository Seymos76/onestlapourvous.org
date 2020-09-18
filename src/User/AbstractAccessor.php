<?php


namespace App\User;


use Doctrine\ORM\EntityManagerInterface;

class AbstractAccessor
{
    protected EntityManagerInterface $entityManager;

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}