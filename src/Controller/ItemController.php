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

            $this->addFlash('success', 'Item successfully created!');
            $this->addFlash('info', 'Pressing back will not delete the item');

            return $this->redirectToRoute('app_item_file_edit_redirect',
                ['userId' => $user->getId(), 'catId' => $category->getId(), 'itemId' => $item->getId(), 'redirect' => true]);
        }
        return $this->render('item/create.html.twig', ['form' => $form->createView(),
            'warrantyToggled' => $item->warrantyToggled(),
            'additionalToggled' => $item->additionalToggled()]);
    }

    #[Route('/users/{userId}/categories/{catId}/items/{itemId}/edit', name: 'app_item_edit', requirements: ['userId' => '\d+', 'catId' => '\d+', 'itemId' => '\d+'],  defaults: ['redirect' => false])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[Entity('item', options: ['id' => 'itemId'])]
    public function edit(User $user, Category $category, Item $item, bool $redirect, Request $request): Response
    {
        $this->denyAccessUnlessGranted('access', $user);
        $form = $this->createForm(ItemType::class, $item, [
            'categories' => $user->getCategories()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->itemRepository->save($item, true);
            $this->addFlash('success', 'Item successfully updated!');

            return $this->redirectToRoute('app_items_category', [
                'userId' => $user->getId(),
                'catId' => $item->getCategory()->getId()
            ]);
        }

        return $this->render('item/edit.html.twig', [
            'category' => $category,
            'item' => $item,
            'form' => $form->createView(),
            'warrantyToggled' => $item->warrantyToggled(),
            'additionalToggled' => $item->additionalToggled()
        ]);
    }


}