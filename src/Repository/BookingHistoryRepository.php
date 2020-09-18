<?php

namespace App\Repository;

use App\Entity\BookingHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BookingHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookingHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookingHistory[]    findAll()
 * @method BookingHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookingHistory::class);
    }

    // /**
    //  * @return BookingHistory[] Returns an array of BookingHistory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BookingHistory
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
