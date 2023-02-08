<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Item;
use App\Entity\User;
use App\Factory\ItemFactory;
use App\Form\FilterItemsType;
use App\Form\ItemType;
use App\Repository\ItemFileRepository;
use App\Repository\ItemRepository;
use App\Repository\WarrantyRepository;
use App\Service\ItemService;
use App\Service\UploaderHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ItemController extends AbstractController
{

    private ItemService $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }











    #[Route('/users/{userId}/categories/{catId}/items', name: 'app_items_category', requirements: ['userId' => '\d+', 'catId' => '\d+'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    public function listInCategory(User $user, ?Category $category, Request $request): Response
    {
        $this->denyAccessUnlessGranted('access', $user);
        $this->denyAccessUnlessGranted('access', $category);

        $form = $this->createForm(FilterItemsType::class, null, [
                'sort' => $request->query->get('sort'),
                'name' => $request->query->get('name')]);


        if($request->query->count() > 0)
        {
            $items = $this->itemService->findUserItemsFilter($user->getId(), $category->getId(), $request->query->all());
        }

        else
            $items = $this->itemService->findUserItems($user->getId(), $category->getId());

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            return $this->redirectToRoute('app_items_category',
                ['userId' => $user->getId(), 'catId' => $category->getId(),
                    'sort' => $form->get('sort')->getData(),
                    'name' => $form->get('name')->getData()]);
        }

        return $this->render('item/list_in_category.html.twig', [
            'category' => $category,
            'items' => $items,
            'form' => $form->createView()
            ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }






















    #[Route('/users/{id}/items', name: 'app_items', requirements: ['id' => '\d+'])]
    #[IsGranted('access', 'user')]
    public function listAll(User $user): Response
    {

        $form = $this->createForm(FilterItemsType::class);

        return $this->render('item/list_all.html.twig', [
            'items' => $this->itemService->findUserItems($user->getId()),
            'form' => $form->createView()
        ]);
    }


    #[Route('/users/{userId}/items/create', name: 'app_item_create', requirements: ['userId' => '\d+'], defaults: ['catId' => null])]
    #[Route('/users/{userId}/categories/{catId}/items/create', name: 'app_item_create_category', requirements: ['userId' => '\d+', 'catId' => '\d+'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    public function create(User $user, ?Category $category, Request $request): Response
    {
        $this->denyAccessUnlessGranted('access', $user);
        $item = ItemFactory::createItem($user);

        if ($category) {
            $this->denyAccessUnlessGranted('access', $category);
            $item->setCategory($category);
        }

        $form = $this->createForm(ItemType::class, $item, [
            'categories' => $user->getCategories(),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $category = $item->getCategory();
            $category->addItem($item);
            $this->itemService->save($item, true);

            $this->addFlash('success', 'Item successfully created!');
            $this->addFlash('info', 'Pressing back will not delete the item');

            return $this->redirectToRoute('app_item_file_edit_redirect',
                ['userId' => $user->getId(), 'catId' => $category->getId(), 'itemId' => $item->getId(), 'redirect' => true]);
        }
        return $this->render('item/create.html.twig', ['form' => $form->createView(),
            'warrantyToggled' => $item->warrantyToggled(),
            'additionalToggled' => $item->additionalToggled()
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
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

        $oldExpiration = null;
        if( $item->getWarranty() !== null)
            $oldExpiration = clone $item->getWarranty()->getExpiration();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $this->itemService->handleWarrantyChanges($item, $oldExpiration);
            $this->itemService->save($item, true);
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
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }

    #[Route('/users/{userId}/categories/{catId}/items/{itemId}/delete', name: 'app_item_delete', requirements: ['userId' => '\d+', 'catId' => '\d+', 'itemId' => '\d+'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[Entity('item', options: ['id' => 'itemId'])]
    public function delete(User $user, Category $category, Item $item, Request $request): Response
    {
        $this->denyAccessUnlessGranted('access', $user);
        $this->itemService->remove($item, true);
        $this->addFlash('item_success', 'Item successfully deleted!');
        
        return match ($request->query->get('from')) {
            'all' => $this->redirectToRoute('app_items', ['id' => $user->getId()]),
            'notifications' => $this->redirectToRoute('app_notifications', ['id' => $user->getId()]),
            default => $this->redirectToRoute('app_items_category', ['userId' => $user->getId(), 'catId' => $category->getId()]),
        };

    }

    #[Route('/users/{id}/items/search', name: '_app_item_search', requirements: ['id' => '\d+'])]
    public function searchUserItems(int $id, Request $request): Response
    {
        $term = $request->query->get('term');
        return $this->render('item/_search_preview.html.twig', [
            'items' => $this->itemService->findUserItems($id, null, $term)
        ]);
    }
}