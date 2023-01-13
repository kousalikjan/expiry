<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends BaseController
{
    #[Route('/user/{id}', name: 'app_profile', requirements: ['id' => '\d+'])]
    public function index(int $id): Response
    {
        $this->checkOwner($id);

        return $this->render('profile/index.html.twig', ['user' => $this->getUser()]);
    }
}