<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;

class CategoryService
{
    private CategoryRepository $categoryRepository;
    private ItemService $itemService;

    public function __construct(CategoryRepository $categoryRepository, ItemService $itemService)
    {
        $this->categoryRepository = $categoryRepository;
        $this->itemService = $itemService;
    }

    public function find(int $id): ?Category
    {
        return $this->categoryRepository->find($id);
    }

    public function save(Category $category, bool $flush = false): void
    {
        $this->categoryRepository->save($category, $flush);
    }

    public function removeAndRemoveAllItems(Category $category, bool $flush = false): void
    {
        foreach ($category->getItems() as $item)
        {
            $this->itemService->remove($item);
        }
        $this->categoryRepository->remove($category, true);


    }


}