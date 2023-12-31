<?php

namespace App\Service;

use App\Entity\Item;
use App\Repository\ItemFileRepository;
use App\Repository\ItemRepository;
use App\Repository\WarrantyRepository;
use League\Flysystem\FilesystemException;

class ItemService
{
    private ItemRepository $itemRepository;
    private WarrantyRepository $warrantyRepository;
    private ItemFileRepository $itemFileRepository;
    private UploaderHelper $uploaderHelper;

    public function __construct(ItemRepository $itemRepository, WarrantyRepository $warrantyRepository, ItemFileRepository $itemFileRepository, UploaderHelper $uploaderHelper)
    {
        $this->itemRepository = $itemRepository;
        $this->warrantyRepository = $warrantyRepository;
        $this->itemFileRepository = $itemFileRepository;
        $this->uploaderHelper = $uploaderHelper;
    }

    public function save(Item $item, bool $flush = false): void
    {
        $this->itemRepository->save($item, $flush);
    }

    /**
     * Removes an item, deleting all its associated files
     *
     * @param Item $item the item to remove
     * @param bool $flush whether to flush the entity manager after removing the item
     * @throws FilesystemException
     */
    public function remove(Item $item, bool $flush = false): void
    {
        foreach ($item->getItemFiles() as $file)
        {
            $this->uploaderHelper->deleteFile($file->getItemFilePath());
            if($file->isThumbnail())
                $this->uploaderHelper->deleteFile($file->getItemFileThumbnailPath());
            $this->itemFileRepository->remove($file);
        }
        $this->itemRepository->remove($item, $flush);
    }

    public function find(int $id): Item|null
    {
        return $this->itemRepository->find($id);
    }

    /**
     * Checks whether an item's expiration date has been changed, if so, resets its notifications
     *
     * @param Item $item item whose expiration date might have been changed
     * @param \DateTime|null $oldExpiration old expiration date of the given item's warranty
     */
    public function handleWarrantyChanges(Item $item, ?\DateTime $oldExpiration): void
    {
        if($item->getWarranty() !== null && $item->getWarranty()->getExpiration() === null)
        {
            // User has toggled off the warranty checkbox
            $this->removeWarranty($item);
        }

        if($item->getWarranty()?->getExpiration() !== null && $item->getWarranty()->getExpiration() != $oldExpiration )
        {
            // User has changed expiration date
            $item->getWarranty()->setNotifiedByEmail(false);
            $item->getWarranty()->setNotificationCleared(false);
        }
    }

    private function removeWarranty(Item $item): void
    {
        $this->warrantyRepository->remove($item->getWarranty());
        $item->setNullWarranty();
    }

    public function findUserItems(int $userId, ?int $catId = null, ?string $term = null): array
    {
        return $this->itemRepository->findUserItems($userId, $catId, $term);
    }

    public function findUserItemsUniqueNamesByTerm(int $userId, string $term): array
    {
        return $this->itemRepository->findUserItemsUniqueNamesByTerm($userId , $term);
    }

    public function getUserItemsCount(int $userId): int
    {
        return $this->itemRepository->getUserItemsCount($userId);
    }

    public function findUserItemsAndFilter(int $userId, ?int $catId = null, array $query = []): array
    {
        $includeExpired = $query['includeExpired'] ?? '1';

        return $this->itemRepository->findUserItemsFilter(
            $userId,
            $catId,
            $query['name'] ?? null,
            $query['vendor'] ?? null,
            $query['expiresIn'] ?? null,
            $includeExpired === '1',
            $query['sort'] ?? null);
    }

    public function findToBeNotifiedInAppOneUserByCleared(int $userId, bool $cleared): array
    {
        return $this->itemRepository->findToBeNotifiedInAppOneUserByCleared($userId, $cleared);
    }

    public function clearNotification(Item $item): void
    {
        if($item->getWarranty() === null)
            return;

        $warranty = $item->getWarranty();
        $warranty->setNotificationCleared(true);
        $this->warrantyRepository->save($warranty, true);
    }
}