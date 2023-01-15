<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Item;
use App\Form\CategoryType;
use App\Form\ItemType;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
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


    #[Route('/users/{userId}/items/create', name: 'app_item_create', requirements: ['userId' => '\d+'], defaults: ['catId' => null])]
    #[Route('/users/{userId}/categories/{catId}/items/create', name: 'app_item_create_category', requirements: ['userId' => '\d+', 'catId' => '\d+'])]
    public function createEdit(int $userId, ?int $catId, Request $request): Response
    {
        $user = $this->findOrFailUser($userId);
        $category = null;
        if($catId !== null){
            $category = $this->findOrFailCategory($catId);
        }

        $item = new Item();
        $form = $this->createForm(ItemType::class, $item, [
            'categories' => $user->getCategories()
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            dd($form);
        }
        return $this->render('item/create_edit.html.twig', ['form' => $form->createView(), 'create' => $catId === null, 'category' => $category]);

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