<?php

namespace App\Service;

use App\Entity\Item;
use App\Repository\ItemFileRepository;
use App\Repository\ItemRepository;
use App\Repository\WarrantyRepository;

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
        }

    }

    private function removeWarranty(Item $item): void
    {
        $this->warrantyRepository->remove($item->getWarranty());
        $item->setNullWarranty();
    }

    public function findUserItems(int $userId, ?string $term = null): array
    {
        return $this->itemRepository->findUserItems($userId, $term);
    }

    public function findCategoryItemsAndSort(int $catId, ?string $sort): array
    {
        return match ($sort) {
            'name', 'amount' => $this->itemRepository->findCategoryItemsAndSortBy($catId, $sort),
            default => $this->itemRepository->findCategoryItemsSortByExpiration($catId),
        };
    }

    public function findToBeNotifiedInAppOneUserByCleared(int $userId, bool $cleared): array
    {
        return $this->itemRepository->findToBeNotifiedInAppOneUserByCleared($userId, $cleared);
    }

    public function clearNotification(Item $item): void
    {
        if($item->getWarranty() === null)
            return;

        $warranty =$item->getWarranty();
        $warranty->setNotificationCleared(true);
        $this->warrantyRepository->save($warranty, true);
    }

}