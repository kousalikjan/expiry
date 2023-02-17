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


    #[Route('/users/{userId}/items', name: 'app_items', requirements: ['userId' => '\d+'])]
    #[Route('/users/{userId}/categories/{catId}/items', name: 'app_items_category', requirements: ['userId' => '\d+', 'catId' => '\d+'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[IsGranted('access', 'user')]
    public function list(User $user, ?Category $category, Request $request): Response
    {
        if($category !== null)
            $this->denyAccessUnlessGranted('access', $category);

        $request->getSession()->set('itemsUrl', $request->getRequestUri());

        $form = $this->createForm(FilterItemsType::class, null, [
                'name' => $request->query->get('name'),
                'vendor' => $request->query->get('vendor'),
                'expiresIn' => $request->query->get('expiresIn'),
                'includeExpired' => $request->query->get('includeExpired'),
                'sort' => $request->query->get('sort')
            ]);

        $items = $request->query->count() > 0
            ? $this->itemService->findUserItemsFilter($user->getId(), $category?->getId(), $request->query->all())
            : $this->itemService->findUserItems($user->getId(), $category?->getId());


        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            return $this->redirectToRoute(
                $category === null ? 'app_items' : 'app_items_category', [
                    'userId' => $user->getId(),
                    'catId' => $category?->getId(),
                    'name' => $form->get('name')->getData(),
                    'vendor' => $form->get('vendor')->getData(),
                    'expiresIn' => $form->get('expiresIn')->getData(),
                    'includeExpired' => $form->get('includeExpired')->getData() === false ? '0' : null,
                    'sort' => $form->get('sort')->getData(),
                    ]);
        }

        return $this->render('item/list.html.twig', [
            'category' => $category,
            'items' => $items,
            'form' => $form->createView(),
            'visibleFilters' => $request->query->count() > 0,
            'resetUrl' => $request->getPathInfo()
            ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }

    #[Route('/users/{userId}/items/create', name: 'app_item_create', requirements: ['userId' => '\d+'], defaults: ['catId' => null])]
    #[Route('/users/{userId}/categories/{catId}/items/create', name: 'app_item_create_category', requirements: ['userId' => '\d+', 'catId' => '\d+'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[IsGranted('access', 'user')]
    public function create(User $user, ?Category $category, Request $request): Response
    {
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
    #[IsGranted('access', 'user')]
    #[IsGranted('access', 'category')]
    #[IsGranted('access', 'item')]
    public function edit(User $user, Category $category, Item $item, Request $request): Response
    {

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

            if($request->getSession()->get('itemsUrl'))
                return $this->redirect( $request->getSession()->get('itemsUrl'));

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
    #[IsGranted('access', 'user')]
    #[IsGranted('access', 'category')]
    #[IsGranted('access', 'item')]
    public function delete(User $user, Category $category, Item $item, Request $request): Response
    {

        $this->itemService->remove($item, true);
        $this->addFlash('item_success', 'Item successfully deleted!');

        if($request->getSession()->get('itemsUrl'))
            return $this->redirect( $request->getSession()->get('itemsUrl'));

        return $this->redirectToRoute('app_items_category', ['userId' => $user->getId(), 'catId' => $category->getId()]);

    }

    #[Route('/users/{id}/items/search', name: '_app_item_search', requirements: ['id' => '\d+'])]
    #[IsGranted('access', 'user')]
    public function searchUserItems(User $user, Request $request): Response
    {
        $term = $request->query->get('term');

        return $this->render('item/_search_preview.html.twig', [
            'items' => $this->itemService->findUserItems($user->getId(), null, $term)
        ]);
    }
}