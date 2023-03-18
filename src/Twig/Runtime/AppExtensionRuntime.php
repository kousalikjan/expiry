<?php

namespace App\Twig\Runtime;

use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use Twig\Extension\RuntimeExtensionInterface;

class AppExtensionRuntime implements RuntimeExtensionInterface
{

    private ItemRepository $itemRepository;
    private CategoryRepository $categoryRepository;

    public function __construct(ItemRepository $itemRepository, CategoryRepository $categoryRepository)
    {
        $this->itemRepository = $itemRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function notificationsCount(int $userId): int
    {
        return sizeof($this->itemRepository->findToBeNotifiedInAppOneUserByCleared($userId, false));
    }

    public function categoriesCount(int $userId): int
    {
        return $this->categoryRepository->getUserCategoriesCount($userId);
    }
}
