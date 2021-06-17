<?php

namespace App\Repository;

use App\Entity\Campus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Campus|null find($id, $lockMode = null, $lockVersion = null)
 * @method Campus|null findOneBy(array $criteria, array $orderBy = null)
 * @method Campus[]    findAll()
 * @method Campus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CampusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campus::class);
    }

    public function findLike($value)
    {
        $queryBuilder=$this->createQueryBuilder('v');
        $queryBuilder->andWhere('v.nom LIKE :val');
        $queryBuilder->setParameter('val', '%'.$value.'%');
        $queryBuilder->addOrderBy('v.nom','ASC');
        $query= $queryBuilder->getQuery();
        $results=$query->getResult(); //utiliser getOne si on récupère qu'un résultat car n'est pas ss forme de tableau
        return $results;
    }

}
