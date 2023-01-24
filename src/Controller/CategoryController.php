<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Item;
use App\Entity\User;
use App\Form\CategoryType;
use App\Form\SelectCategoryType;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use App\Service\CategoryService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    private CategoryService $categoryService;


    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    #[Route('/users/{id}/categories', name: 'app_categories', requirements: ['id' => '\d+'])]
    public function index(User $user): Response
    {
        $this->denyAccessUnlessGranted('access', $user);
        return $this->render('category/index.html.twig', [
            'categories' => $user->getCategories()]);
    }

    #[Route('/users/{userId}/categories/create', name: 'app_category_create', requirements: ['userId' => '\d+'], defaults: ['catId' => null])]
    #[Route('/users/{userId}/categories/{catId}/edit', name: 'app_category_edit', requirements: ['userId' => '\d+', 'catId' => '\d+'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    public function createEdit(User $user, ?Category $category, Request $request): Response
    {
        // So I can't create categories for other users
        $this->denyAccessUnlessGranted('access', $user);

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
        return $this->render('category/create_edit.html.twig', ['form' => $form->createView(), 'create' => !$category->getId(), 'category' => $category]);

    }


    #[Route('/users/{userId}/categories/{catId}/delete', name: 'app_category_delete', requirements: ['userId' => '\d+', 'catId' => '\d+'])]
    #[Entity('user', options: ['id' => 'userId'])]
    #[Entity('category', options: ['id' => 'catId'])]
    public function delete(User $user, Category $category, Request $request): Response
    {

        $form = $this->createForm(SelectCategoryType::class, null, [
            'allCategories' => array_filter($user->getCategories()->toArray(), fn (Category $elem) => $elem->getId() !== $category->getId() ),
            'current' => $category->getName()
        ]);
        $this->denyAccessUnlessGranted('access', $category);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $moveToCategory = $form->get('category')->getData();
            $this->categoryService->remove($category, $moveToCategory, true);
            return $this->redirectToRoute('app_categories', ['id' => $user->getId()]);
        }
        return $this->render('category/delete.html.twig', ['category' => $category, 'form' => $form]);
    }

}
