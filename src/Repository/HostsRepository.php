<?php

namespace App\Repository;

use App\Entity\Hosts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Hosts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hosts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hosts[]    findAll()
 * @method Hosts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HostsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hosts::class);
    }

    // /**
    //  * @return Hosts[] Returns an array of Hosts objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Hosts
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function search($mots = null){
        $query = $this->createQueryBuilder('a');
        $query->where('a.active = 1');
        if($mots != null){
            $query->andWhere('MATCH_AGAINST(a.name, a.description, a.website, a.legal_number) AGAINST (:mots boolean)>0')
                ->setParameter('mots', '*'.$mots.'*');
        }
        return $query->getQuery()->getResult();
    }
}
