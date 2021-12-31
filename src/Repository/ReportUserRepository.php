<?php

namespace App\Repository;

use App\Entity\ReportUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReportUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReportUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReportUser[]    findAll()
 * @method ReportUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportUser::class);
    }

    // /**
    //  * @return ReportUser[] Returns an array of ReportUser objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReportUser
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
