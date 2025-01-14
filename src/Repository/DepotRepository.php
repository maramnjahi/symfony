<?php

namespace App\Repository;

use App\Entity\Depot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Depot>
 *
 * @method Depot|null find($id, $lockMode = null, $lockVersion = null)
 * @method Depot|null findOneBy(array $criteria, array $orderBy = null)
 * @method Depot[]    findAll()
 * @method Depot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Depot::class);
    }

    /**
     * @return Depot[] Returns an array of Depot objects
     */
   // public function findByExampleField($value): array
    //{
       // return $this->createQueryBuilder('p')
         //   ->andWhere('p.exampleField = :val')
         //   ->setParameter('val', $value)
         //   ->orderBy('p.id', 'ASC')
          //  ->setMaxResults(10)
          //  ->getQuery()
          //  ->getResult();
   // }

   // public function findOneBySomeField($value): ?Depot
    //{
       // return $this->createQueryBuilder('p')
    //        ->andWhere('p.exampleField = :val')
     //       ->setParameter('val', $value)
      //      ->getQuery()
          //  ->getOneOrNullResult();
  // }
}

