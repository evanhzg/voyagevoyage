<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<City>
 *
 * @method City|null find($id, $lockMode = null, $lockVersion = null)
 * @method City|null findOneBy(array $criteria, array $orderBy = null)
 * @method City[]    findAll()
 * @method City[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    public function save(City $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(City $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findWithPagination(int $page, int $limit, string $orderBy, string $orderByDirection, array $filters){
        $query = $this->createQueryBuilder('c')
            ->orderBy("c." . $orderBy, $orderByDirection)
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit)
            ->where('c.status = true');
        foreach ($filters as $filterKey => $filterValue) {
            $query->andWhere('c.' . $filterKey . $filterValue );
        }
        return $query->getQuery()
            ->getResult();
    }

//    /**
//     * @param Country $country
//     * @return City[] Returns an array of City objects
//     */
//    public function findByRandomCity(Country $country): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.country =' . $country)
//            ->getQuery()
//            ->getResult()
//        ;
    }

//    public function findOneBySomeField($value): ?City
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
