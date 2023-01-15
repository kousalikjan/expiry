<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends BaseController
{

    private ItemRepository $itemRepository;
    private CategoryRepository $categoryRepository;

    public function __construct(UserRepository $userRepository, ItemRepository $itemRepository, CategoryRepository $categoryRepository)
    {
        parent::__construct($userRepository);
        $this->itemRepository = $itemRepository;
        $this->categoryRepository = $categoryRepository;
    }

    #[Route('/users/{userId}/categories/{catId}/items',
        name: 'app_items_category',
        requirements: ['userId' => '\d+', 'catId' => '\d+'])]
    public function index(int $userId, int $catId): Response
    {
        $this->findOrFailUser($userId); // Obligatory, checks for id in URL only
        $category = $this->findOrFailCategory($catId);

        return $this->render('item/index.html.twig', [
            'category' => $category]);
    }

    private function findOrFailCategory(int $id): Category
    {
        $category = $this->categoryRepository->find($id);
        if($category === null)
            throw $this->createNotFoundException();
        $this->denyAccessUnlessGranted('access', $category);
        return $category;
    }

}