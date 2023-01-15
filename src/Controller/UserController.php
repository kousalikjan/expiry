<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends BaseController
{
    #[Route('/users/{id}', name: 'app_profile', requirements: ['id' => '\d+'])]
    public function index(int $id): Response
    {
        $user = $this->findOrFailUser($id);
        return $this->render('profile/index.html.twig', ['user' => $user]);
    }
}