<?php

namespace App\Repository;

use App\Entity\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Department|null find($id, $lockMode = null, $lockVersion = null)
 * @method Department|null findOneBy(array $criteria, array $orderBy = null)
 * @method Department[]    findAll()
 * @method Department[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }

    public function findByParams(array $params)
    {
        $query = $this->createQueryBuilder('d')
            ->where('d.country = :country')
            ->setParameter('country', $params["country_filter"] ?? 'fr');

        return $query->getQuery()->getResult();
    }

    public function findByCodeLike(string $code)
    {
        return $this->createQueryBuilder('d')
            ->where('d.code LIKE :code')
            ->setParameter('code', $code)
            ->getQuery()->getResult();
    }

    public function findByCountryWithNativeSql(?string $country)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT id, name, code FROM department d
        WHERE d.country = :country
        ORDER BY d.code ASC 
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['country' => $country ?? 'fr']);
        return $stmt->fetchAll();
    }

    // /**
    //  * @return Department[] Returns an array of Department objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Department
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
