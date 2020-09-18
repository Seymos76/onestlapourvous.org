<?php

namespace App\Repository;

use App\Entity\Therapist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Therapist|null find($id, $lockMode = null, $lockVersion = null)
 * @method Therapist|null findOneBy(array $criteria, array $orderBy = null)
 * @method Therapist[]    findAll()
 * @method Therapist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TherapistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Therapist::class);
    }

    // /**
    //  * @return Therapist[] Returns an array of Therapist objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Therapist
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
