<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Item>
 *
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function save(Item $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Item $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Item[] Items of given user
     */
    public function findUserItems(int $userId) : array
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.category', 'i_category')
            ->innerJoin('i_category.owner', 'i_owner')
            ->andWhere('i_owner.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('i_category.id')
            ->getQuery()
            ->getResult();
    }

    public function findCategoryItemsAndSort(int $catId, string $sort): array
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.category', 'i_category')
            ->andWhere('i_category.id = :catId')
            ->setParameter('catId', $catId)
            ->orderBy('i.' . $sort)
            ->getQuery()
            ->getResult();
    }

    public function findCategoryItemsSortWarranty(int $catId): array
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.category', 'i_category')
            ->leftJoin('i.warranty', 'w')
            ->andWhere('i_category.id = :catId')
            ->setParameter('catId', $catId)
            ->orderBy('w.expiration')
            ->getQuery()
            ->getResult();
    }

    public function findNotifiedItems(int $userId): array
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.category', 'i_category')
            ->innerJoin('i_category.owner', 'i_owner')
            ->andWhere('i_owner.id = :userId')
            ->setParameter('userId', $userId)
            ->innerJoin('i.warranty', 'i_warranty')
            ->andWhere('i_warranty.notifiedByEmail = true')
            ->andWhere('i_warranty.notificationCleared = false')
            ->orderBy('i_category.id')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Item[] Returns an array of Item objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Item
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


}
