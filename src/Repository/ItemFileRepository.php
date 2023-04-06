<?php

namespace App\Repository;

use App\Entity\ItemFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ItemFile>
 *
 * @method ItemFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemFile[]    findAll()
 * @method ItemFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemFile::class);
    }

    public function save(ItemFile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ItemFile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ItemFile[] array of item files that are images
     */
    public function findImageFiles(int $itemId): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.mimeType LIKE :image')
            ->setParameter(':image', '%'.'image'.'%')
            ->andWhere('f.item = :itemId')
            ->setParameter('itemId', $itemId)
            ->getQuery()
            ->getResult();
    }
}
