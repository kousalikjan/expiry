<?php

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use League\Flysystem\FilesystemException;

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

    /**
     * Removes a category and optionally moves its items to another category
     *
     * @param Category $category the category to remove
     * @param Category|null $moveToCategory the category to move the items to. If null, the items will be removed
     * @param bool $flush whether to flush the entity manager after removing the category
     * @throws FilesystemException
     */
    public function remove(Category $category, ?Category $moveToCategory, bool $flush = false): void
    {
        if($moveToCategory === null)
        {
            foreach ($category->getItems() as $item)
            {
                $this->itemService->remove($item);
            }
        }
        else
        {
            foreach ($category->getItems() as $item)
            {
                $category->removeItem($item);
                $moveToCategory->addItem($item);
            }
        }
        $this->categoryRepository->remove($category, true);
    }


}