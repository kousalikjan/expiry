<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Item;
use App\Entity\User;
use App\Form\ItemType;
use App\Repository\ItemRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends AbstractController
{

    private ItemRepository $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    #[Route('/users/{userId}/categories/{catId}/items',
        name: 'app_items_category',
        requirements: ['userId' => '\d+', 'catId' => '\d+'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    public function index(User $user, Category $category): Response
    {
        $this->denyAccessUnlessGranted('access', $user);
        $this->denyAccessUnlessGranted('access', $category);

        return $this->render('item/index.html.twig', [
            'category' => $category]);
    }

    #[Route('/users/{userId}/items/create', name: 'app_item_create', requirements: ['userId' => '\d+'], defaults: ['catId' => null])]
    #[Route('/users/{userId}/categories/{catId}/items/create', name: 'app_item_create_category', requirements: ['userId' => '\d+', 'catId' => '\d+'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    public function create(User $user, ?Category $category, Request $request): Response
    {
        $this->denyAccessUnlessGranted('access', $user);
        $item = new Item();

        if ($category) {
            $this->denyAccessUnlessGranted('access', $category);
            $item->setCategory($category);
        }

        $form = $this->createForm(ItemType::class, $item, [
            'categories' => $user->getCategories()
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $category = $item->getCategory();
            $category->addItem($item);
            $this->itemRepository->save($item, true);

            return $this->redirectToRoute('app_item_file_edit',
                ['userId' => $user->getId(), 'catId' => $category->getId(), 'itemId' => $item->getId()]);
        }
        return $this->render('item/create.html.twig', ['form' => $form->createView(), 'toggled' => $form->isSubmitted()]);
    }

    #[Route('users/{userId}/categories/{catId}/items/{itemId}/edit', name: 'app_item_file_edit')]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[Entity('item', options: ['id' => 'itemId'])]
    public function edit(User $user, Category $category, Item $item): Response
    {
        $this->denyAccessUnlessGranted('access', $item);

        return $this->render('item/files.html.twig', [
            'user' => $user,
            'category' => $category,
            'item' => $item]);
    }

}