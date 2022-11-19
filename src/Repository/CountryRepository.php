<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

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

    public function findWithPagination(int $page, int $limit, string $orderBy, string $orderByDirection, array $filters){
        $query = $this->createQueryBuilder('c')
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit)
            ->orderBy("c." . $orderBy, $orderByDirection)
            ->where('c.status = true');
        foreach ($filters as $filterKey => $filterValue) {
            $query->andWhere('c.' . $filterKey . $filterValue );
        }

        return $query->getQuery()
            ->getResult();
    }

// evan :   Tentative de fonction pour le retour aléatoire de Place et City pour un pays donné
//          N'a pas fonctionné, le queryBuilder demande des ressources que je n'avais pas sur le coup, le getDoctrine et getRepository
//          nécessitent d'extend la classe à la classe Controller de Doctrine mais trop de difficultés à combiner des extensions de classe.
//
//          Une autre tentative vaine à été faite, par la connexion de plusieurs fonctions dans les repositories de chaque classe mais
//          la relation entre chaque poserait le meme souci.
//
//          Une derniere option serait de tout fairee dans un controlleur, plus simple
//          mais incohérent aveec l'organisation de symfony.
//
//          J'abandonne ici mais en laissant des traces de mes dernières volontés (il est 8h du matin, je bosse dans 3h :/)
//
//    public function randTrip()
//    {
//        $city = new CityRepository;
//        $city = $city->createQueryBuilder('c')->where('city.country = ' . $this)->first();
//        $hotel = shuffle(Place::where('city = ' . $city)->where('type = hotel'))->first();
//        $restaurant = shuffle(Place::where('city = ' . $city)->where('type = restaurant'))->first();
//        $touristic_place = shuffle(Place::where('city = ' . $city)->where('type = touristic_place'))->first();
//    }

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
