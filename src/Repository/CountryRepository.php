<?php

namespace App\Repository;

use DateTime;
use DateTimeImmutable;
use App\Entity\Country;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\Parameter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Country>
 *
 * @method Country|null find($id, $lockMode = null, $lockVersion = null)
 * @method Country|null findOneBy(array $criteria, array $orderBy = null)
 * @method Country[]    findAll()
 * @method Country[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CountryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Country::class);
    }

    public function save(Country $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Country $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findWithPagination(int $page, int $limit){
        return $this->createQueryBuilder('c')
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit)
            ->where('c.status = true')
            ->getQuery()
            ->getResult();
    }

    public function findBetweenDates(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        int $page,
        int $limit
    ){
        $startDate = $startDate ? $startDate : new DateTimeImmutable();
        $qb = $this->createQueryBuilder('c');
        $qb->add(
            'where',
            $qb->expr()->orX(
                $qb->expr()->andX(
                    $qb->expr()->gte('c.dateStart', 'startDate'),
                    $qb->expr()->lte('c.dateStart', 'endDate')
                ),
                $qb->expr()->andX(
                    $qb->expr()->gte('c.dateEnd', 'startDate'),
                    $qb->expr()->lte('c.dateEnd', 'endDate')
                )
            )
        )
        ->setParameters(new ArrayCollection([
            new Parameter('startDate', $startDate, Types::DATETIME_IMMUTABLE),
            new Parameter('endDate', $endDate, Types::DATETIME_IMMUTABLE)
        ]));
    }

//    /**
//     * @return Country[] Returns an array of Country objects
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

//    public function findOneBySomeField($value): ?Country
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
