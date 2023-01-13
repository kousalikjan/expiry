<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\CategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends BaseController
{

    private CategoryService $categoryService;

    public function __construct(UserRepository $userRepository, CategoryService $categoryService)
    {
        parent::__construct($userRepository);
        $this->categoryService = $categoryService;
    }

    #[Route('/category/{id}', name: 'app_category', requirements: ['id' => '\d+'])]
    public function index(int $id): Response
    {
        $this->checkOwner($id);
        return $this->render('category/index.html.twig', [
            'categories' => $this->getUser()->getCategories()]);
    }
}