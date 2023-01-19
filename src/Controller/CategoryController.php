<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
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
            $this->categoryRepository->save($category, true);
            return $this->redirectToRoute('app_categories', ['id' => $user->getId()]);
        }
        return $this->render('category/create_edit.html.twig', ['form' => $form->createView(), 'create' => !$category->getId(), 'category' => $category]);

    }
}
