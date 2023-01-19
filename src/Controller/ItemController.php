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

            return $this->redirectToRoute('app_items_category',
                ['userId' => $user->getId(), 'catId' => $category->getId()]);
        }
        return $this->render('item/create_edit.html.twig', ['form' => $form->createView()]);
    }
}