<?php

namespace App\Repository;

use App\Entity\UpcomingEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UpcomingEvent>
 *
 * @method UpcomingEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method UpcomingEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method UpcomingEvent[]    findAll()
 * @method UpcomingEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UpcomingEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UpcomingEvent::class);
    }

//    /**
//     * @return UpcomingEvent[] Returns an array of UpcomingEvent objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UpcomingEvent
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
