<?php

namespace App\Repository;

use App\Entity\Places;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Places>
 *
 * @method Places|null find($id, $lockMode = null, $lockVersion = null)
 * @method Places|null findOneBy(array $criteria, array $orderBy = null)
 * @method Places[]    findAll()
 * @method Places[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlacesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Places::class);
    }

    public function save(Places $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Places $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Places[] Returns an array of Places objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Places
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
