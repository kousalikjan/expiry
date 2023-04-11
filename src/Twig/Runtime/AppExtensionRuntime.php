<?php

namespace App\Twig\Runtime;

use App\Repository\CategoryRepository;
use App\Repository\ItemFileRepository;
use App\Repository\ItemRepository;
use App\Service\ItemFileService;
use Twig\Extension\RuntimeExtensionInterface;

class AppExtensionRuntime implements RuntimeExtensionInterface
{

    private ItemRepository $itemRepository;
    private ItemFileService $itemFileService;
    private CategoryRepository $categoryRepository;

    public function __construct(ItemRepository $itemRepository, ItemFileService $itemFileService, CategoryRepository $categoryRepository)
    {
        $this->itemRepository = $itemRepository;
        $this->itemFileService = $itemFileService;
        $this->categoryRepository = $categoryRepository;
    }

    public function notificationsCount(int $userId): int
    {
        return $this->itemRepository->getAppNotificationsCount($userId);
    }

    public function categoriesCount(int $userId): int
    {
        return $this->categoryRepository->getUserCategoriesCount($userId);
    }

    public function findItemImageId(int $itemId): int|null
    {
        return $this->itemFileService->findImageFileId($itemId);
    }
}
