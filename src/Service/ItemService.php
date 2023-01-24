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

    public function removeWarranty(Item $item): void
    {
        $this->warrantyRepository->remove($item->getWarranty());
        $item->setNullWarranty();
    }

    public function remove(Item $item, bool $flush = false)
    {
        foreach ($item->getItemFiles() as $file)
        {
            $this->uploaderHelper->deleteFile($file->getItemFilePath());
            $this->itemFileRepository->remove($file);
        }
        $this->itemRepository->remove($item, $flush);
    }

}