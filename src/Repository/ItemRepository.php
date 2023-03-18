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
     * @return Item[] Items of given user
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
            $qb->andWhere('LOWER(i.name) LIKE LOWER(:term) OR LOWER(i.vendor) LIKE LOWER(:term)')
                ->setParameter('term', '%'.$term.'%');
        }

        return
            $qb
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
