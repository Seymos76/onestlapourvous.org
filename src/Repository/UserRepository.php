<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function findRecentlyRegistered(string $from, string $to)
    {
        $dateFrom = new \DateTime($from);
        $dateTo = new \DateTime($to);
        $from = new \DateTime($dateFrom->format("Y-m-d")." 00:00:00");
        $to = new \DateTime($dateTo->format("Y-m-d")." 23:59:59");
        return $this->createQueryBuilder('u')
            ->where('u.createdAt BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()->getResult()
            ;
    }

    public function findTodayRegistered()
    {
        $dateToday = new \DateTime('now');
        $from = new \DateTime($dateToday->format("Y-m-d")." 00:00:00");
        $to = new \DateTime($dateToday->format("Y-m-d")." 23:59:59");
        return $this->createQueryBuilder('u')
            ->where('u.createdAt BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()->getResult()
            ;
    }

    public function findByParams(array $params = null)
    {
        $query = $this->createQueryBuilder('u')
            ->where('u.isActive = :isActive')
            ->setParameter('isActive', true);

        if (isset($params['email_filter'])) {
            $query->andWhere("u.email LIKE :email")
                ->setParameter('email', $params['email_filter']);
        }
        if (isset($params['lastname_filter'])) {
            $query->andWhere('u.lastName = :lastname')
                ->setParameter('lastname', $params['lastname_filter']);
        }
        if (isset($params['firstname_filter'])) {
            $query->andWhere('u.firstName = :firstname')
                ->setParameter('firstname', $params['firstname_filter']);
        }
        return $query->getQuery()->getResult();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
