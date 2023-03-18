<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Form\CategoryType;
use App\Form\SelectCategoryType;
use App\Service\CategoryService;
use App\Service\ItemService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CategoryController extends AbstractController
{
    private CategoryService $categoryService;
    private ItemService $itemService;

    public function __construct(CategoryService $categoryService, ItemService $itemService)
    {
        $this->categoryService = $categoryService;
        $this->itemService = $itemService;
    }

    #[Route('/users/{id}/categories', name: 'app_categories', requirements: ['id' => '\d+'])]
    #[IsGranted('access', 'user')]
    public function list(User $user): Response
    {
        return $this->render('category/list.html.twig', [
            'categories' => $user->getCategories(),
            'itemsCount' => $this->itemService->getUserItemsCount($user->getId())
        ]);
    }

    #[Route('/users/{userId}/categories/create', name: 'app_category_create', requirements: ['userId' => '\d+'], defaults: ['catId' => null])]
    #[Route('/users/{userId}/categories/{catId}/edit', name: 'app_category_edit', requirements: ['userId' => '\d+', 'catId' => '\d+'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[IsGranted('access', 'user')]
    public function createEdit(User $user, ?Category $category, Request $request): Response
    {
        if($category)
            $this->denyAccessUnlessGranted('access', $category);
        else
            $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $user->addCategory($category);
            $this->categoryService->save($category, true);
            $this->addFlash('success', 'Category successfully created!');
            return $this->redirectToRoute('app_categories', ['id' => $user->getId()]);
        }

        return $this->render('category/create_edit.html.twig', [
            'form' => $form->createView(),
            'create' => !$category->getId(),
            'category' => $category
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }

    #[Route('/users/{userId}/categories/{catId}/delete', name: 'app_category_delete', requirements: ['userId' => '\d+', 'catId' => '\d+'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    #[IsGranted('access', 'user')]
    #[IsGranted('access', 'category')]
    public function delete(User $user, Category $category, Request $request): Response
    {
        $form = $this->createForm(SelectCategoryType::class, null, [
            'allCategories' => array_filter($user->getCategories()->toArray(), fn (Category $elem) => $elem->getId() !== $category->getId())
        ]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $moveToCategory = $form->get('category')->getData();
            $this->categoryService->remove($category, $moveToCategory, true);
            $this->addFlash('success', 'Category successfully deleted!');
            return $this->redirectToRoute('app_categories', ['id' => $user->getId()]);
        }

        return $this->render('category/delete.html.twig', [
            'category' => $category,
            'form' => $form
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }
}
