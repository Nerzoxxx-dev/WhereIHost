<?php

namespace App\Repository;

use App\Entity\ReportHost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReportHost|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReportHost|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReportHost[]    findAll()
 * @method ReportHost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportHostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportHost::class);
    }

    // /**
    //  * @return ReportHost[] Returns an array of ReportHost objects
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
    public function findOneBySomeField($value): ?ReportHost
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
