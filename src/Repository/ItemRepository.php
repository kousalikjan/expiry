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
    public function findUserItems(int $userId, ?string $term = null) : array
    {
        $qb = $this->createQueryBuilder('i')
                ->innerJoin('i.category', 'i_category')
                ->innerJoin('i_category.owner', 'i_owner')
                ->andWhere('i_owner.id = :userId')
                ->setParameter('userId', $userId);

        if($term) {
            $qb->andWhere('LOWER(i.name) LIKE LOWER(:term) OR LOWER(i.vendor) LIKE LOWER(:term)')
                ->setParameter('term', '%'.$term.'%');
        }

        return
            $qb->getQuery()
            ->getResult();
    }

    /**
     * @return Item[] Items of given user
     */
    public function findCategoryItemsAndSortBy(int $catId, string $sort): array
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.category', 'i_category')
            ->andWhere('i_category.id = :catId')
            ->setParameter('catId', $catId)
            ->orderBy('i.' . $sort)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Item[] Items of given user
     */
    public function findCategoryItemsSortByExpiration(int $catId): array
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

    /**
     * @return Item[] Items that should be notified in app (not cleared)
     */
    public function findToBeNotifiedInAppOneUserByCleared(int $userId, bool $cleared): array
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.warranty', 'w')
            ->innerJoin('i.category', 'c')
            ->innerJoin('c.owner', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('w.notificationCleared = :cleared')
            ->setParameter('cleared', $cleared)
            ->andWhere('w.notifyDaysBefore is not null')
            ->andWhere('current_date() >= w.expiration - w.notifyDaysBefore')
            ->orderBy('w.expiration')
            ->getQuery()
            ->getResult();
    }

}
