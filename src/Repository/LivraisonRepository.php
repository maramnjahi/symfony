<?php

namespace App\Repository;

use App\Entity\Livraison;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Livraison>
 *
 * @method Livraison|null find($id, $lockMode = null, $lockVersion = null)
 * @method Livraison|null findOneBy(array $criteria, array $orderBy = null)
 * @method Livraison[]    findAll()
 * @method Livraison[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LivraisonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livraison::class);
    }

    /**
     * @return Livraison[] Returns an array of Livraison objects
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

   // public function findOneBySomeField($value): ?Livraison
    //{
       // return $this->createQueryBuilder('p')
    //        ->andWhere('p.exampleField = :val')
     //       ->setParameter('val', $value)
      //      ->getQuery()
          //  ->getOneOrNullResult();
  // }
}