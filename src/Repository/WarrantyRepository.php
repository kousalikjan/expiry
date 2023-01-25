<?php

namespace App\Repository;

use App\Entity\Warranty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Warranty>
 *
 * @method Warranty|null find($id, $lockMode = null, $lockVersion = null)
 * @method Warranty|null findOneBy(array $criteria, array $orderBy = null)
 * @method Warranty[]    findAll()
 * @method Warranty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WarrantyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Warranty::class);
    }

    public function save(Warranty $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Warranty $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Warranty[] Warranties to be notified for given user
     */
    public function findToBeNotifiedOfOneUser(int $userId): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.notified = false')
            ->andWhere('w.notifyDaysBefore is not null')
            ->andWhere('current_date() >= w.expiration - w.notifyDaysBefore')
            ->innerJoin('w.item', 'w_item')
            ->innerJoin('w_item.category', 'w_category')
            ->innerJoin('w_category.owner', 'w_owner')
            ->andWhere('w_owner.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }


//    /**
//     * @return Warranty[] Returns an array of Warranty objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('w.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Warranty
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
