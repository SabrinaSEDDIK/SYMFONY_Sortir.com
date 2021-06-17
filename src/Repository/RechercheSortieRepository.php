<?php

namespace App\Repository;

use App\Entity\RechercheSortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RechercheSortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method RechercheSortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method RechercheSortie[]    findAll()
 * @method RechercheSortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RechercheSortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RechercheSortie::class);
    }

    // /**
    //  * @return RechercheSortie[] Returns an array of RechercheSortie objects
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
    public function findOneBySomeField($value): ?RechercheSortie
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
