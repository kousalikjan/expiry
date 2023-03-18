<?php

namespace App\Service;

use App\Entity\ItemFile;
use App\Repository\ItemFileRepository;

class ItemFileService
{
    private ItemFileRepository $itemFileRepository;

    public function __construct(ItemFileRepository $itemFileRepository)
    {
        $this->itemFileRepository = $itemFileRepository;
    }

    public function save(ItemFile $itemFile, bool $flush = false): void
    {
        $this->itemFileRepository->save($itemFile, $flush);
    }

    public function remove(ItemFile $itemFile, bool $flush = false): void
    {
        $this->itemFileRepository->remove($itemFile, $flush);
    }

    public function findImageFiles(int $id): array
    {
        return $this->itemFileRepository->findImageFiles($id);
    }

}