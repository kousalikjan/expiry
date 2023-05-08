<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
     * @param int $userId user id
     * @param int|null $catId category id of the item
     * @param string|null $name name of the item
     * @param string|null $vendor vendor of the item
     * @param string|null $expireIn in how many days the item expires
     * @param bool $includeExpired whether already expired items should be included
     * @param string|null $sort ASC or DESC
     * @return Item[] items of the given user matching the given filters
     */
    public function findUserItemsFilter(int $userId, ?int $catId, ?string $name, ?string $vendor, ?string $expireIn, bool $includeExpired, ?string $sort): array
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.warranty', 'w')
            ->innerJoin('i.category', 'c')
            ->innerJoin('c.owner', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId);

        if($catId !== null)
        {
            $qb->andWhere('c.id = :catId')
                ->setParameter('catId', $catId);
        }

        if($name !== null) {
            $qb->andWhere('LOWER(i.name) LIKE LOWER(:name)')
                ->setParameter('name', '%'.$name.'%', ParameterType::STRING);
        }

        if($vendor !== null) {
            $qb->andWhere('LOWER(i.vendor) LIKE LOWER(:vendor)')
                ->setParameter('vendor', '%'.$vendor.'%');
        }

        if($expireIn !== null) {

            $expireBefore = new \DateTime();
            $expireBefore->modify("+".$expireIn.' days');

            $qb->andWhere('w IS NOT NULL')
                ->andWhere('w.expiration <= :expires' )
                ->setParameter('expires', $expireBefore);
        }

        if($includeExpired === false) {
            $qb->andWhere('w IS NULL OR w.expiration >= current_date()');
        }


        switch ($sort)
        {
            case 'expiration':
                $qb->orderBy('w.expiration');
                break;
            case 'amount':
                $qb->orderBy('i.amount');
                break;
            default:
                $qb->orderBy('LOWER(i.name)');
                break;
        }

        return $qb->getQuery()
            ->getResult();

    }


    /**
     * @param int $userId user id
     * @param int|null $catId category id of the item
     * @param string|null $term searched name
     * @return Item[] items whose name matches the given term of the given user
     */
    public function findUserItems(int $userId, ?int $catId = null, ?string $term = null) : array
    {
        $qb = $this->createQueryBuilder('i')
            ->innerJoin('i.category', 'c')
            ->innerJoin('c.owner', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId);

        if($catId !== null)
        {
            $qb->andWhere('c.id = :catId')
                ->setParameter('catId', $catId);
        }

        if($term) {
            $qb->andWhere('LOWER(i.name) LIKE LOWER(:term)')
                ->setParameter('term', $term.'%');
        }

        return $qb
            ->orderBy('LOWER(i.name)')
            ->getQuery()
            ->getResult();
    }


    public function getUserItemsCount(int $userId) : int
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.category', 'c')
            ->innerJoin('c.owner', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->select('count(i.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int $userId user id
     * @param bool $cleared whether cleared items should be returned
     * @return Item[] items that are to be notified or have already been cleared in app of the given user
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
            ->andWhere(':today >= w.expiration - w.notifyDaysBefore')
            ->setParameter('today', new \DateTime())
            ->orderBy('w.expiration')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $userId user id
     * @return int count of not cleared notifications of the given user
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getAppNotificationsCount(int $userId): int
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.warranty', 'w')
            ->innerJoin('i.category', 'c')
            ->innerJoin('c.owner', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('w.notificationCleared = false')
            ->andWhere('w.notifyDaysBefore is not null')
            ->andWhere(':today >= w.expiration - w.notifyDaysBefore')
            ->setParameter('today', new \DateTime())
            ->select('count(i.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int $userId user id
     * @param string $term searched name
     * @return string[] unique names of items whose name matches the given term of the given user
     */
    public function findUserItemsUniqueNamesByTerm(int $userId, string $term) : array
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.category', 'c')
            ->innerJoin('c.owner', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('LOWER(i.name) LIKE LOWER(:term)')
            ->setParameter('term', $term.'%')
            ->select('i.name')
            ->orderBy('i.name')
            ->distinct()
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

}
