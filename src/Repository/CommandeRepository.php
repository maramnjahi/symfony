<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 *
 * @method Commande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commande[]    findAll()
 * @method Commande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }
    public function findrepasByNom($etat)
{
    $queryBuilder = $this->createQueryBuilder('c');

    if (!empty($etat)) {
        // Perform a case-insensitive search by converting both the search query and the database field to lowercase
        $queryBuilder->where('LOWER(c.etat) LIKE :etat')
                     ->setParameter('etat', '%' . strtolower($etat) . '%');
    }

    return $queryBuilder->getQuery()->getResult();
}

//    /**
//     * @return Commande[] Returns an array of Commande objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Commande
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
public function findByUserNomAsc()
{
    return $this->createQueryBuilder('c')
        ->leftJoin('c.user', 'u') // Assuming 'user' is the property that links to the User entity
        ->orderBy('u.nom', 'ASC')
        ->getQuery()
        ->getResult();
}
public function findByUserNomDesc()
{
    return $this->createQueryBuilder('c')
        ->leftJoin('c.user', 'u') // Assuming 'user' is the property that links to the User entity
        ->orderBy('u.nom', 'DESC')
        ->getQuery()
        ->getResult();
}
public function findByDateAsc()
{
    return $this->createQueryBuilder('c')
        ->orderBy('c.cmdDate', 'ASC')
        ->getQuery()
        ->getResult();
}

public function findByDateDesc()
{
    return $this->createQueryBuilder('c')
        ->orderBy('c.cmdDate', 'DESC')
        ->getQuery()
        ->getResult();
}
}
