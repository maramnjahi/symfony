<?php

namespace App\Repository;

use App\Entity\Enchere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Enchere>
 *
 * @method Enchere|null find($id, $lockMode = null, $lockVersion = null)
 * @method Enchere|null findOneBy(array $criteria, array $orderBy = null)
 * @method Enchere[]    findAll()
 * @method Enchere[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnchereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enchere::class);
    }

//    /**
//     * @return Enchere[] Returns an array of Enchere objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Enchere
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

public function findByMinAmount(float $minAmount)
{
    return $this->createQueryBuilder('e')
        ->andWhere('e.montantInitial >= :minAmount')
        ->setParameter('minAmount', $minAmount)
        ->getQuery()
        ->getResult();
}

public function searchByName(string $name)
{
    return $this->createQueryBuilder('e')
        ->andWhere('e.nomEnchere LIKE :name')
        ->setParameter('name', '%'.$name.'%')
        ->getQuery()
        ->getResult();
}
public function searchById(string $idclcreree ){
    return $this->createQueryBuilder('e')
    ->andWhere('e.idclcreree LIKE :idclcreree')
    ->setParameter('idclcreree', '%'.$idclcreree.'%')
    ->getQuery()
    ->getResult();
}


public function sortData()
    {
        $select = $_GET['sort-data']; // Assuming you are using GET method to send the sorting option

        if ($select === "lth") {
            $furnitureData = $this->entityManager->getRepository(Enchere::class)->findBy([], ['montantInitial' => 'ASC']);
        } elseif ($select === "htl") {
            $furnitureData = $this->entityManager->getRepository(Enchere::class)->findBy([], ['montantInitial' => 'DESC']);
        } elseif ($select === "reset") {
            // Redirect to the same page to reset sorting
            return $this->redirectToRoute('app_enchere_index');
        }

        // Now you have $furnitureData sorted based on montantInitial, you can pass it to Twig template for rendering
        return $this->render('enchere/index.html.twig', [
            'furnitureData' => $furnitureData,
        ]);
    }

}
