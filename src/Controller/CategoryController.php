<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Service\CategoryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends BaseController
{
    private CategoryRepository $categoryRepository;

    public function __construct(UserRepository $userRepository, CategoryRepository $categoryRepository)
    {
        parent::__construct($userRepository);
        $this->categoryRepository = $categoryRepository;
    }

    #[Route('/users/{id}/categories', name: 'app_categories', requirements: ['id' => '\d+'])]
    public function index(int $id): Response
    {
        $this->checkOwner($id);
        return $this->render('category/index.html.twig', [
            'categories' => $this->getUser()->getCategories()]);
    }

    #[Route('/users/{userId}/categories/create', name: 'app_category_create', requirements: ['userId' => '\d+'], defaults: ['catId' => null])]
    #[Route('/users/{userId}/categories/{catId}/edit', name: 'app_category_edit', requirements: ['userId' => '\d+', 'catId' => '\d+'])]
    public function createEdit(int $userId, ?int $catId, Request $request, MailerInterface $mailer): Response
    {
        $this->checkOwner($userId);
        $category = $catId !== null ? $this->findOrFail($catId) : new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $this->getUser()->addCategory($category);
            $this->categoryRepository->save($category, true);
            return $this->redirectToRoute('app_categories', ['id' => $userId]);
        }
        return $this->render('category/create_edit.html.twig', ['form' => $form->createView(), 'create' => $catId === null]);

    }

    private function findOrFail(int $id): Category
    {
        $category = $this->categoryRepository->find($id);
        if($category === null)
            throw $this->createNotFoundException();
        return $category;
    }

}
